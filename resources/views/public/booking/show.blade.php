<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $link->title }} | Booking</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f4efe7;
            --card: #fffdf9;
            --ink: #1f2937;
            --muted: #6b7280;
            --line: #dfd4c4;
            --accent: #0f766e;
            --accent-soft: #d9f3ef;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Georgia, "Times New Roman", serif;
            color: var(--ink);
            background:
                radial-gradient(circle at top left, rgba(15,118,110,.18), transparent 26%),
                linear-gradient(180deg, #fcf8f2 0%, var(--bg) 100%);
            min-height: 100vh;
        }
        .shell {
            max-width: 1080px;
            margin: 0 auto;
            padding: 40px 20px 64px;
        }
        .hero {
            display: grid;
            gap: 24px;
            align-items: start;
        }
        .panel {
            background: rgba(255,253,249,.94);
            border: 1px solid var(--line);
            border-radius: 24px;
            padding: 28px;
            box-shadow: 0 18px 50px rgba(31,41,55,.08);
        }
        .eyebrow {
            margin: 0 0 10px;
            font: 600 12px/1.2 Arial, sans-serif;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: var(--accent);
        }
        h1 {
            margin: 0;
            font-size: clamp(2.1rem, 5vw, 4rem);
            line-height: .96;
        }
        p { color: var(--muted); }
        .grid {
            display: grid;
            gap: 18px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .field {
            display: grid;
            gap: 8px;
        }
        .field.full { grid-column: 1 / -1; }
        label {
            font: 600 13px/1.2 Arial, sans-serif;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        input, select, textarea, button {
            width: 100%;
            border-radius: 14px;
            border: 1px solid var(--line);
            padding: 14px 16px;
            font: inherit;
            background: white;
        }
        textarea { min-height: 120px; resize: vertical; }
        button {
            border: 0;
            background: var(--accent);
            color: white;
            font: 600 15px/1.2 Arial, sans-serif;
            letter-spacing: .03em;
            cursor: pointer;
        }
        .meta {
            display: grid;
            gap: 12px;
            margin-top: 24px;
        }
        .meta-card {
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 18px;
            background: var(--accent-soft);
        }
        .alert {
            border-radius: 16px;
            padding: 14px 16px;
            margin-bottom: 18px;
        }
        .alert.success {
            background: #dcfce7;
            color: #166534;
        }
        .alert.error {
            background: #fee2e2;
            color: #991b1b;
        }
        .errors { margin: 0; padding-left: 18px; }
        @media (max-width: 720px) {
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="shell">
        <div class="hero">
            <div class="panel">
                <p class="eyebrow">Public Booking</p>
                <h1>{{ $link->title }}</h1>
                <p>{{ $link->description ?: 'Choose a time inside the published booking window and the app will create the scheduled work order automatically.' }}</p>

                <div class="meta">
                    <div class="meta-card">
                        <strong>Booking Window</strong>
                        <p>{{ \Illuminate\Support\Carbon::parse($link->start_time)->format('g:i A') }} - {{ \Illuminate\Support\Carbon::parse($link->end_time)->format('g:i A') }} in {{ $link->timezone }}</p>
                    </div>
                    <div class="meta-card">
                        <strong>Slot Length</strong>
                        <p>{{ $link->slot_minutes }} minutes, up to {{ $link->max_days_ahead }} days in advance.</p>
                    </div>
                </div>
            </div>

            <div class="panel">
                @if ($success)
                    <div class="alert success">{{ $success }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert error">
                        <ul class="errors">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('public-booking.store', ['token' => $link->token]) }}">
                    @csrf

                    <div class="grid">
                        <div class="field full">
                            <label for="service_id">Service</label>
                            <select id="service_id" name="service_id" @disabled($link->service_id)>
                                @foreach ($serviceOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('service_id', $link->service_id) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field">
                            <label for="name">Name</label>
                            <input id="name" name="name" value="{{ old('name') }}" required>
                        </div>

                        <div class="field">
                            <label for="email">Email</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required>
                        </div>

                        <div class="field">
                            <label for="phone">Phone</label>
                            <input id="phone" name="phone" value="{{ old('phone') }}">
                        </div>

                        <div class="field">
                            <label for="booking_date">Booking Date</label>
                            <input id="booking_date" type="date" name="booking_date" value="{{ old('booking_date') }}" required>
                        </div>

                        <div class="field">
                            <label for="start_time">Start Time</label>
                            <input id="start_time" type="time" name="start_time" value="{{ old('start_time') }}" required>
                        </div>

                        <div class="field full">
                            <label for="address_line">Service Address</label>
                            <textarea id="address_line" name="address_line">{{ old('address_line') }}</textarea>
                        </div>

                        <div class="field full">
                            <label for="notes">Notes</label>
                            <textarea id="notes" name="notes">{{ old('notes') }}</textarea>
                        </div>

                        <div class="field full">
                            <button type="submit">Book This Time</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>