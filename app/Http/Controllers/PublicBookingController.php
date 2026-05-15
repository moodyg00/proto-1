<?php

namespace App\Http\Controllers;

use App\Models\Availability;
use App\Models\Booking;
use App\Models\Contact;
use App\Models\PublicBookingLink;
use App\Models\Service;
use App\Models\WorkOrder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PublicBookingController extends Controller
{
    public function show(string $token): View
    {
        $link = $this->resolveLink($token);

        return view('public.booking.show', [
            'link' => $link,
            'serviceOptions' => $this->getServiceOptions($link),
            'success' => session('booking_success'),
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $link = $this->resolveLink($token);

        $data = $request->validate([
            'service_id' => ['nullable', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:60'],
            'booking_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'address_line' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $service = $this->resolveService($link, $data['service_id'] ?? null);
        $start = Carbon::parse($data['booking_date'] . ' ' . $data['start_time'], $link->timezone);
        $end = $start->copy()->addMinutes((int) $link->slot_minutes);

        $this->guardBookingWindow($link, $start, $end);

        DB::transaction(function () use ($data, $service, $start, $end): void {
            $contact = Contact::query()->firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'phone' => $data['phone'] ?: null,
                    'type' => 'customer',
                    'status' => 'active',
                    'address' => $data['address_line'] ? ['formatted' => $data['address_line']] : null,
                ],
            );

            $contact->forceFill([
                'name' => $data['name'],
                'phone' => $data['phone'] ?: $contact->phone,
                'address' => $data['address_line'] ? ['formatted' => $data['address_line']] : $contact->address,
            ])->save();

            $workOrder = WorkOrder::query()->create([
                'work_order_number' => 'WO-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
                'contact_id' => $contact->getKey(),
                'customer_name' => $contact->name,
                'service_id' => $service?->getKey(),
                'service_name' => $service?->name,
                'status' => 'scheduled',
                'scheduled_date' => $start->toDateString(),
                'booking_date' => $start->toDateString(),
                'booking_time' => $start->format('H:i:s'),
                'address' => $data['address_line'] ? ['formatted' => $data['address_line']] : null,
                'notes' => $data['notes'] ? ['public_booking' => $data['notes']] : null,
            ]);

            Booking::query()->create([
                'work_order_id' => $workOrder->getKey(),
                'booking_date' => $start->toDateString(),
                'start_time' => $start->format('H:i:s'),
                'end_time' => $end->format('H:i:s'),
                'address' => $workOrder->address,
                'notes' => $data['notes'] ?: 'Booked from public booking link.',
            ]);
        });

        return redirect()
            ->route('public-booking.show', ['token' => $token])
            ->with('booking_success', 'Booking requested successfully. The job is now on the schedule.');
    }

    protected function resolveLink(string $token): PublicBookingLink
    {
        return PublicBookingLink::query()
            ->with('service')
            ->where('token', $token)
            ->where('is_active', true)
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->firstOrFail();
    }

    protected function getServiceOptions(PublicBookingLink $link): array
    {
        if ($link->service) {
            return [$link->service->getKey() => $link->service->name];
        }

        return Service::query()
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    protected function resolveService(PublicBookingLink $link, ?string $serviceId): ?Service
    {
        if ($link->service) {
            return $link->service;
        }

        return $serviceId ? Service::query()->findOrFail($serviceId) : null;
    }

    protected function guardBookingWindow(PublicBookingLink $link, Carbon $start, Carbon $end): void
    {
        $dayOfWeek = $start->dayOfWeekIso;
        $allowedWeekdays = collect($link->available_weekdays ?: [1, 2, 3, 4, 5])->map(fn ($day): int => (int) $day)->all();

        if (! in_array($dayOfWeek, $allowedWeekdays, true)) {
            throw ValidationException::withMessages([
                'booking_date' => 'That date is not available for this booking link.',
            ]);
        }

        $allowedStart = Carbon::parse($start->toDateString() . ' ' . $link->start_time, $link->timezone);
        $allowedEnd = Carbon::parse($start->toDateString() . ' ' . $link->end_time, $link->timezone);
        $lastBookableDate = now($link->timezone)->addDays((int) $link->max_days_ahead)->endOfDay();

        if ($start->lt(now($link->timezone)) || $start->gt($lastBookableDate)) {
            throw ValidationException::withMessages([
                'booking_date' => 'Choose a date within the active booking window for this link.',
            ]);
        }

        if ($start->lt($allowedStart) || $end->gt($allowedEnd)) {
            throw ValidationException::withMessages([
                'start_time' => 'That time falls outside the booking hours for this link.',
            ]);
        }

        $availabilityRecords = Availability::query()
            ->where('is_available', true)
            ->where('starts_at', '<=', $start->copy()->endOfDay())
            ->where('ends_at', '>=', $start->copy()->startOfDay())
            ->get();

        if ($availabilityRecords->isNotEmpty()) {
            $covered = $availabilityRecords->contains(fn (Availability $availability): bool =>
                $availability->starts_at->lte($start) && $availability->ends_at->gte($end)
            );

            if (! $covered) {
                throw ValidationException::withMessages([
                    'start_time' => 'That time is outside the currently published availability.',
                ]);
            }
        }

        $hasConflict = Booking::query()
            ->whereDate('booking_date', $start->toDateString())
            ->whereNotNull('start_time')
            ->where(function ($query) use ($start, $end): void {
                $query
                    ->whereBetween('start_time', [$start->format('H:i:s'), $end->format('H:i:s')])
                    ->orWhereBetween('end_time', [$start->format('H:i:s'), $end->format('H:i:s')])
                    ->orWhere(function ($nestedQuery) use ($start, $end): void {
                        $nestedQuery
                            ->where('start_time', '<=', $start->format('H:i:s'))
                            ->where('end_time', '>=', $end->format('H:i:s'));
                    });
            })
            ->exists();

        if ($hasConflict) {
            throw ValidationException::withMessages([
                'start_time' => 'That time is already booked. Choose another slot.',
            ]);
        }
    }
}