<x-filament-panels::page
    @class([
        'fi-resource-list-records-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
    ])
>
    <div class="flex flex-col gap-y-6">
        <x-filament-panels::resources.tabs />

        <section class="app-surface-panel lead-board-banner sticky top-[4.75rem] z-[1] rounded-xl border px-4 py-3 shadow-sm backdrop-blur">
            <div class="flex items-center justify-between gap-4 max-lg:flex-wrap">
                <div class="flex flex-col gap-1">
                    <div class="text-sm text-slate-500 dark:text-slate-400">Operations</div>
                    <div class="text-2xl font-semibold tracking-tight text-slate-950 dark:text-slate-50">Schedule</div>
                </div>

                <div class="flex items-center gap-3 max-sm:w-full max-sm:flex-wrap max-sm:justify-end">
                    {{ $this->createBookingAction }}
                </div>
            </div>
        </section>

        <div class="hidden">
            {{ $this->editBookingAction }}
        </div>

        <div
            x-data="scheduleCalendar()"
            x-init="init()"
            x-on:schedule-bookings-updated.window="refetchEvents()"
            class="app-surface-panel rounded-2xl border p-4 shadow-sm md:p-6"
        >
            <div class="mb-4 flex flex-col gap-1">
                <h2 class="text-lg font-semibold text-slate-950 dark:text-slate-50">Booking Calendar</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Click any day to add a booking. Switch between month, week, and day views to manage scheduled jobs.</p>
            </div>

            <div x-ref="calendar" class="schedule-calendar min-h-[46rem]"></div>
        </div>

        <x-filament-actions::modals />
    </div>

    @once
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
        <script>
            function scheduleCalendar() {
                return {
                    calendar: null,
                    componentId: null,
                    async init() {
                        if (!window.FullCalendar) {
                            return;
                        }

                        this.componentId = this.$root.closest('[wire\\:id]')?.getAttribute('wire:id') ?? null;

                        this.calendar = new window.FullCalendar.Calendar(this.$refs.calendar, {
                            initialView: 'dayGridMonth',
                            buttonText: {
                                today: 'today',
                                month: 'month',
                                week: 'week',
                                day: 'day',
                            },
                            headerToolbar: {
                                left: 'prev,next today',
                                center: 'title',
                                right: 'dayGridMonth,timeGridWeek,timeGridDay',
                            },
                            height: 'auto',
                            contentHeight: 'auto',
                            aspectRatio: 1.5,
                            editable: false,
                            selectable: true,
                            nowIndicator: true,
                            dayMaxEvents: true,
                            fixedWeekCount: true,
                            expandRows: true,
                            eventTimeFormat: {
                                hour: 'numeric',
                                minute: '2-digit',
                                meridiem: 'short',
                            },
                            events: async (fetchInfo, successCallback, failureCallback) => {
                                try {
                                    const component = this.componentId ? window.Livewire?.find(this.componentId) : null;
                                    const events = component
                                        ? await component.call('getCalendarEvents', fetchInfo.startStr, fetchInfo.endStr)
                                        : [];
                                    successCallback(events);
                                } catch (error) {
                                    failureCallback(error);
                                }
                            },
                            eventContent: (arg) => {
                                const workOrderNumber = arg.event.extendedProps.workOrderNumber || arg.event.title;
                                const isMonthView = arg.view.type === 'dayGridMonth';
                                const isMobile = window.matchMedia('(max-width: 640px)').matches;
                                const timeText = arg.timeText || '';

                                if (isMonthView && isMobile) {
                                    return {
                                        html: '<span class="schedule-calendar__dot" aria-hidden="true"></span><span class="sr-only">' + workOrderNumber + '</span>',
                                    };
                                }

                                return {
                                    html: `
                                        <span class="schedule-calendar__event-inner">
                                            ${timeText ? `<span class="schedule-calendar__event-time">${timeText}</span>` : ''}
                                            <span class="schedule-calendar__event-label">${workOrderNumber}</span>
                                        </span>
                                    `,
                                };
                            },
                            dateClick: (info) => {
                                const isMobile = window.matchMedia('(max-width: 640px)').matches;

                                if (isMobile && this.calendar.view.type === 'dayGridMonth') {
                                    this.calendar.changeView('timeGridDay', info.dateStr);
                                    return;
                                }

                                this.$wire.mountAction('createBooking', { date: info.dateStr });
                            },
                            eventClick: (info) => {
                                this.$wire.mountAction('editBooking', { booking: info.event.id });
                            },
                        });

                        this.calendar.render();
                    },
                    refetchEvents() {
                        if (this.calendar) {
                            this.calendar.refetchEvents();
                        }
                    },
                };
            }
        </script>
        <style>
            .schedule-calendar .fc {
                --fc-border-color: var(--app-border-soft);
                --fc-page-bg-color: var(--app-surface-panel);
                --fc-neutral-bg-color: var(--app-surface-raised);
                --fc-list-event-hover-bg-color: var(--app-surface-inset);
                --fc-today-bg-color: rgba(247, 184, 75, 0.12);
                color: var(--app-text);
            }

            .schedule-calendar .fc .fc-toolbar-title {
                color: var(--app-text);
                font-size: 1.1rem;
                font-weight: 700;
            }

            .schedule-calendar .fc .fc-button {
                background: var(--app-surface-raised);
                border-color: var(--app-border-soft);
                color: var(--app-text);
                box-shadow: none;
                text-transform: capitalize;
            }

            .schedule-calendar .fc .fc-button-primary:not(:disabled).fc-button-active,
            .schedule-calendar .fc .fc-button-primary:not(:disabled):active {
                background: #f7b84b;
                border-color: #f7b84b;
                color: #111827;
            }

            .schedule-calendar .fc .fc-event {
                border-radius: 10px;
                padding: 0;
                font-weight: 600;
                overflow: hidden;
            }

            .schedule-calendar .fc-theme-standard td,
            .schedule-calendar .fc-theme-standard th,
            .schedule-calendar .fc-theme-standard .fc-scrollgrid {
                border-color: var(--app-border-soft);
            }

            .schedule-calendar .fc .fc-daygrid-day-frame {
                min-height: 8.25rem;
                padding: 0.35rem;
            }

            .schedule-calendar .fc .fc-daygrid-day-top {
                justify-content: flex-end;
            }

            .schedule-calendar .fc .fc-daygrid-day-events {
                margin-top: 0.35rem;
                overflow: hidden;
            }

            .schedule-calendar .fc .fc-daygrid-event-harness,
            .schedule-calendar .fc .fc-daygrid-event {
                max-width: 100%;
            }

            .schedule-calendar .fc .fc-daygrid-block-event .fc-event-main,
            .schedule-calendar .fc .fc-timegrid-event .fc-event-main {
                padding: 0;
            }

            .schedule-calendar__event-inner {
                display: flex;
                align-items: center;
                gap: 0.35rem;
                width: 100%;
                min-width: 0;
                padding: 0.22rem 0.45rem;
                overflow: hidden;
                white-space: nowrap;
            }

            .schedule-calendar__event-time {
                flex: 0 0 auto;
                font-size: 0.7rem;
                font-weight: 700;
                opacity: 0.95;
            }

            .schedule-calendar__event-label {
                min-width: 0;
                overflow: hidden;
                text-overflow: ellipsis;
                font-size: 0.72rem;
                font-weight: 700;
            }

            .schedule-calendar__dot {
                display: inline-flex;
                width: 0.45rem;
                height: 0.45rem;
                border-radius: 999px;
                background: currentColor;
                margin-inline: 0.2rem;
            }

            @media (max-width: 640px) {
                .schedule-calendar .fc .fc-toolbar {
                    gap: 0.75rem;
                }

                .schedule-calendar .fc .fc-toolbar.fc-header-toolbar {
                    flex-direction: column;
                    align-items: stretch;
                }

                .schedule-calendar .fc .fc-toolbar-chunk {
                    display: flex;
                    justify-content: space-between;
                    gap: 0.5rem;
                    flex-wrap: wrap;
                }

                .schedule-calendar .fc .fc-daygrid-day-frame {
                    min-height: 4.9rem;
                    padding: 0.2rem;
                }

                .schedule-calendar .fc .fc-daygrid-day-events {
                    min-height: 0;
                }

                .schedule-calendar .fc .fc-daygrid-event-harness {
                    margin-top: 0.12rem;
                }

                .schedule-calendar .fc .fc-daygrid-day-bottom {
                    font-size: 0.72rem;
                }
            }
        </style>
    @endonce
</x-filament-panels::page>