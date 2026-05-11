<x-filament-panels::page>
	<style>
		.ops-dashboard {
			--ops-surface-base: var(--app-surface-panel);
			--ops-surface-raised: var(--app-surface-raised);
			--ops-surface-inset: var(--app-surface-inset);
			--ops-border-soft: var(--app-border-soft);
			--ops-border-strong: var(--app-border-strong);
			--ops-text: var(--app-text);
			--ops-text-muted: var(--app-text-muted);
			--ops-text-subtle: var(--app-text-subtle);
			--ops-shadow-panel: var(--app-shadow-panel);
			--ops-shadow-raised: var(--app-shadow-raised);
			display: grid;
			gap: 24px;
			color: var(--ops-text);
		}

		.ops-dashboard__header {
			display: flex;
			justify-content: space-between;
			align-items: flex-end;
			gap: 16px;
			flex-wrap: wrap;
		}

		.ops-dashboard__eyebrow {
			font-size: 0.75rem;
			font-weight: 700;
			letter-spacing: 0.24em;
			text-transform: uppercase;
			color: var(--ops-text-subtle);
		}

		.ops-dashboard__subcopy {
			margin-top: 8px;
			font-size: 0.95rem;
			color: var(--ops-text-muted);
			max-width: 54rem;
		}

		.ops-dashboard__filters {
			display: flex;
			gap: 12px;
			flex-wrap: wrap;
			align-items: center;
		}

		.ops-dashboard__input {
			min-width: 12rem;
			padding: 14px 16px;
			border-radius: 18px;
			border: 1px solid var(--ops-border-strong);
			background: var(--ops-surface-base);
			color: var(--ops-text);
			color-scheme: dark;
		}

		.ops-dashboard__button,
		.ops-dashboard__button:link,
		.ops-dashboard__button:visited {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			padding: 14px 20px;
			border-radius: 18px;
			text-decoration: none;
			font-weight: 600;
		}

		.ops-dashboard__anchor,
		.ops-dashboard__anchor:link,
		.ops-dashboard__anchor:visited {
			color: inherit;
			text-decoration: none;
		}

		.ops-dashboard__anchor:hover .ops-dashboard__item-title,
		.ops-dashboard__anchor:hover .ops-dashboard__table-primary,
		.ops-dashboard__anchor:hover .ops-dashboard__section-title {
			text-decoration: underline;
			text-underline-offset: 0.18em;
		}

		.ops-dashboard__button--primary {
			background: #f7b84b;
			border: 1px solid #f7b84b;
			color: #111827;
		}

		.ops-dashboard__button--ghost {
			background: transparent;
			border: 1px solid var(--ops-border-strong);
			color: var(--ops-text);
		}

		.ops-dashboard__hero,
		.ops-dashboard__panel {
			border: 1px solid var(--ops-border-soft);
			border-radius: 28px;
			background: var(--ops-surface-base);
			box-shadow: var(--ops-shadow-panel), inset 0 1px 0 rgba(255, 255, 255, 0.02);
		}

		.ops-dashboard__hero {
			padding: 24px;
		}

		.ops-dashboard__hero-grid {
			display: grid;
			gap: 16px;
			grid-template-columns: repeat(4, minmax(0, 1fr));
		}

		.ops-dashboard__stat-card,
		.ops-dashboard__metric,
		.ops-dashboard__list-panel {
			border: 1px solid var(--ops-border-soft);
			border-radius: 24px;
			padding: 22px;
			background: var(--ops-surface-raised);
			box-shadow: var(--ops-shadow-raised);
		}

		.ops-dashboard__stat-label,
		.ops-dashboard__section-kicker,
		.ops-dashboard__metric-label {
			font-size: 0.92rem;
			color: var(--ops-text-muted);
		}

		.ops-dashboard__stat-value,
		.ops-dashboard__metric-value {
			margin-top: 14px;
			font-size: 2.1rem;
			font-weight: 700;
			line-height: 1;
		}

		.ops-dashboard__stat-note,
		.ops-dashboard__metric-meta,
		.ops-dashboard__card-note,
		.ops-dashboard__list-meta,
		.ops-dashboard__item-subtitle,
		.ops-dashboard__item-meta {
			margin-top: 10px;
			font-size: 0.92rem;
			color: var(--ops-text-subtle);
		}

		.ops-dashboard__tone--emerald .ops-dashboard__stat-value,
		.ops-dashboard__tone--emerald .ops-dashboard__pill {
			color: #22c55e;
		}

		.ops-dashboard__tone--rose .ops-dashboard__stat-value,
		.ops-dashboard__tone--rose .ops-dashboard__pill {
			color: #fb7185;
		}

		.ops-dashboard__tone--amber .ops-dashboard__stat-value,
		.ops-dashboard__tone--amber .ops-dashboard__pill {
			color: #f59e0b;
		}

		.ops-dashboard__tone--slate .ops-dashboard__stat-value,
		.ops-dashboard__tone--slate .ops-dashboard__pill {
			color: var(--ops-text);
		}

		.ops-dashboard__metrics,
		.ops-dashboard__trends,
		.ops-dashboard__material-highlights {
			display: grid;
			gap: 16px;
			grid-template-columns: repeat(3, minmax(0, 1fr));
		}

		.ops-dashboard__trends {
			grid-template-columns: repeat(4, minmax(0, 1fr));
		}

		.ops-dashboard__columns {
			display: grid;
			gap: 24px;
			grid-template-columns: minmax(0, 1.35fr) minmax(0, 1fr);
		}

		.ops-dashboard__split {
			display: grid;
			gap: 24px;
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}

		.ops-dashboard__section-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			gap: 12px;
			margin-bottom: 18px;
		}

		.ops-dashboard__section-title {
			font-size: 1.15rem;
			font-weight: 700;
		}

		.ops-dashboard__stack,
		.ops-dashboard__list,
		.ops-dashboard__table {
			display: grid;
			gap: 14px;
		}

		.ops-dashboard__bar-item {
			display: grid;
			gap: 10px;
		}

		.ops-dashboard__bar-meta {
			display: flex;
			justify-content: space-between;
			gap: 12px;
			font-size: 0.95rem;
			color: var(--ops-text-muted);
		}

		.ops-dashboard__bar-track {
			height: 14px;
			border-radius: 999px;
			background: var(--ops-surface-inset);
			overflow: hidden;
		}

		.ops-dashboard__bar-fill {
			height: 100%;
			border-radius: inherit;
			background: linear-gradient(90deg, #0ea5e9, #22c55e);
		}

		.ops-dashboard__bar-fill--slate {
			background: linear-gradient(90deg, #64748b, #94a3b8);
		}

		.ops-dashboard__bar-fill--blue {
			background: linear-gradient(90deg, #38bdf8, #0ea5e9);
		}

		.ops-dashboard__bar-fill--amber {
			background: linear-gradient(90deg, #fbbf24, #f59e0b);
		}

		.ops-dashboard__bar-fill--emerald {
			background: linear-gradient(90deg, #4ade80, #22c55e);
		}

		.ops-dashboard__bar-fill--rose {
			background: linear-gradient(90deg, #fb7185, #ef4444);
		}

		.ops-dashboard__list-item,
		.ops-dashboard__table-row {
			display: flex;
			justify-content: space-between;
			gap: 14px;
			padding: 16px 0;
			border-top: 1px solid var(--ops-border-soft);
		}

		.ops-dashboard__list-item:first-child,
		.ops-dashboard__table-row:first-child {
			border-top: 0;
			padding-top: 0;
		}

		.ops-dashboard__item-title,
		.ops-dashboard__table-primary {
			font-size: 0.98rem;
			font-weight: 600;
		}

		.ops-dashboard__trend-delta {
			margin-top: 10px;
			font-size: 0.88rem;
			font-weight: 700;
		}

		.ops-dashboard__trend-delta--emerald {
			color: #22c55e;
		}

		.ops-dashboard__trend-delta--rose {
			color: #fb7185;
		}

		.ops-dashboard__trend-delta--slate {
			color: var(--ops-text-subtle);
		}

		.ops-dashboard__item-value,
		.ops-dashboard__table-secondary {
			text-align: right;
			font-size: 0.95rem;
			font-weight: 600;
		}

		.ops-dashboard__pill {
			display: inline-flex;
			align-items: center;
			padding: 0.3rem 0.65rem;
			border-radius: 999px;
			border: 1px solid currentColor;
			font-size: 0.78rem;
			font-weight: 700;
		}

		.ops-dashboard__empty {
			padding: 18px;
			border: 1px dashed var(--ops-border-strong);
			border-radius: 18px;
			text-align: center;
			color: var(--ops-text-subtle);
		}

		@media (max-width: 1100px) {
			.ops-dashboard__hero-grid,
			.ops-dashboard__metrics,
			.ops-dashboard__trends,
			.ops-dashboard__material-highlights,
			.ops-dashboard__split,
			.ops-dashboard__columns {
				grid-template-columns: repeat(2, minmax(0, 1fr));
			}
		}

		@media (max-width: 760px) {
			.ops-dashboard__hero-grid,
			.ops-dashboard__metrics,
			.ops-dashboard__trends,
			.ops-dashboard__material-highlights,
			.ops-dashboard__split,
			.ops-dashboard__columns {
				grid-template-columns: minmax(0, 1fr);
			}

			.ops-dashboard__list-item,
			.ops-dashboard__table-row {
				flex-direction: column;
			}

			.ops-dashboard__item-value,
			.ops-dashboard__table-secondary {
				text-align: left;
			}
		}
	</style>

	<div class="ops-dashboard">
		<header class="ops-dashboard__header">
			<div>
				<p class="ops-dashboard__eyebrow">Field Operations</p>
				<h2 class="text-3xl font-semibold tracking-tight">Execution, revenue, and risk in one view</h2>
				<p class="ops-dashboard__subcopy">
					Monitor operational bottlenecks, upcoming booked revenue, rework pressure, and material spend without leaving the admin surface.
				</p>
			</div>

			<form method="GET" action="{{ static::getUrl() }}" class="ops-dashboard__filters">
				<input class="ops-dashboard__input" type="date" name="start_date" value="{{ $this->startDate }}">
				<input class="ops-dashboard__input" type="date" name="end_date" value="{{ $this->endDate }}">
				<button class="ops-dashboard__button ops-dashboard__button--primary" type="submit">Apply range</button>
				<a class="ops-dashboard__button ops-dashboard__button--ghost" href="{{ static::getUrl() }}">Reset</a>
			</form>
		</header>

		<section class="ops-dashboard__hero">
			<div class="ops-dashboard__section-header">
				<div>
					<p class="ops-dashboard__section-kicker">Reporting Window</p>
					<h3 class="ops-dashboard__section-title">{{ $this->getRangeLabel() }}</h3>
				</div>
				<p class="ops-dashboard__card-note">Upcoming schedule horizon: {{ $this->getUpcomingLabel() }}</p>
			</div>

			<div class="ops-dashboard__hero-grid">
				@foreach ($this->heroStats as $stat)
					<a class="ops-dashboard__anchor" href="{{ $stat['href'] ?? '#' }}">
						<article class="ops-dashboard__stat-card ops-dashboard__tone--{{ $stat['tone'] }}">
							<p class="ops-dashboard__stat-label">{{ $stat['label'] }}</p>
							<p class="ops-dashboard__stat-value">{{ $stat['value'] }}</p>
							<p class="ops-dashboard__stat-note">{{ $stat['note'] }}</p>
						</article>
					</a>
				@endforeach
			</div>
		</section>

		<section class="ops-dashboard__metrics">
			@foreach ($this->metricCards as $card)
				<a class="ops-dashboard__anchor" href="{{ $card['href'] ?? '#' }}">
					<article class="ops-dashboard__metric ops-dashboard__panel">
						<p class="ops-dashboard__metric-label">{{ $card['label'] }}</p>
						<p class="ops-dashboard__metric-value">{{ $card['value'] }}</p>
						<p class="ops-dashboard__metric-meta">{{ $card['meta'] }}</p>
					</article>
				</a>
			@endforeach
		</section>

		<section class="ops-dashboard__trends">
			@foreach ($this->trendCards as $trend)
				<a class="ops-dashboard__anchor" href="{{ $trend['href'] ?? '#' }}">
					<article class="ops-dashboard__metric ops-dashboard__panel">
						<p class="ops-dashboard__metric-label">{{ $trend['label'] }}</p>
						<p class="ops-dashboard__metric-value">{{ $trend['current_label'] }}</p>
						<p class="ops-dashboard__metric-meta">Prev: {{ $trend['previous_label'] }}</p>
						<p class="ops-dashboard__trend-delta ops-dashboard__trend-delta--{{ $trend['delta_tone'] }}">{{ $trend['delta_label'] }}</p>
						<div class="ops-dashboard__bar-item mt-3">
							<div class="ops-dashboard__bar-meta">
								<span>Current</span>
								<span>Previous</span>
							</div>
							<div class="ops-dashboard__bar-track">
								<div class="ops-dashboard__bar-fill ops-dashboard__bar-fill--{{ $trend['delta_tone'] }}" style="width: {{ $trend['delta_width'] }}%"></div>
							</div>
							<div class="ops-dashboard__bar-track">
								<div class="ops-dashboard__bar-fill ops-dashboard__bar-fill--slate" style="width: {{ $trend['previous_width'] }}%"></div>
							</div>
						</div>
						<p class="ops-dashboard__metric-meta">{{ $trend['meta'] }}</p>
					</article>
				</a>
			@endforeach
		</section>

		<div class="ops-dashboard__columns">
			<section class="ops-dashboard__panel ops-dashboard__list-panel">
				<div class="ops-dashboard__section-header">
					<div>
						<p class="ops-dashboard__section-kicker">Operations Flow</p>
						<h3 class="ops-dashboard__section-title">Status Breakdown</h3>
					</div>
					<p class="ops-dashboard__card-note">Current live pipeline</p>
				</div>

				<div class="ops-dashboard__stack">
					@foreach ($this->statusBreakdown as $status)
						<div class="ops-dashboard__bar-item">
							<div class="ops-dashboard__bar-meta">
								<div>
									<div class="ops-dashboard__item-title">{{ $status['label'] }}</div>
									<div class="ops-dashboard__item-meta">{{ $status['description'] }}</div>
								</div>
								<div class="ops-dashboard__item-value">{{ $this->formatCount($status['value']) }}</div>
							</div>
							<div class="ops-dashboard__bar-track">
								<div class="ops-dashboard__bar-fill ops-dashboard__bar-fill--{{ $status['tone'] }}" style="width: {{ $status['width'] }}%"></div>
							</div>
						</div>
					@endforeach
				</div>
			</section>

			<section class="ops-dashboard__panel ops-dashboard__list-panel">
				<div class="ops-dashboard__section-header">
					<div>
						<p class="ops-dashboard__section-kicker">Booked Revenue</p>
						<h3 class="ops-dashboard__section-title">Upcoming Revenue by Week</h3>
					</div>
					<p class="ops-dashboard__card-note">Scheduled jobs with invoice value</p>
				</div>

				@if (count($this->upcomingRevenue))
					<div class="ops-dashboard__stack">
						@foreach ($this->upcomingRevenue as $row)
							<div class="ops-dashboard__bar-item">
								<div class="ops-dashboard__bar-meta">
									<span>{{ $row['label'] }}</span>
									<span>{{ $this->formatMoney($row['amount']) }} · {{ $this->formatCount($row['jobs']) }} jobs</span>
								</div>
								<div class="ops-dashboard__bar-track">
									<div class="ops-dashboard__bar-fill" style="width: {{ $row['width'] }}%"></div>
								</div>
							</div>
						@endforeach
					</div>
				@else
					<div class="ops-dashboard__empty">No scheduled invoiced work orders were found in the upcoming horizon.</div>
				@endif
			</section>
		</div>

		<div class="ops-dashboard__split">
			<section class="ops-dashboard__panel ops-dashboard__list-panel">
				<div class="ops-dashboard__section-header">
					<div>
						<p class="ops-dashboard__section-kicker">Revenue Focus</p>
						<h3 class="ops-dashboard__section-title">Top Upcoming Jobs</h3>
					</div>
					<p class="ops-dashboard__card-note">Ranked by invoice value</p>
				</div>

				@if (count($this->upcomingJobs))
					<div class="ops-dashboard__list">
						@foreach ($this->upcomingJobs as $job)
							<div class="ops-dashboard__list-item">
								<div>
									<div class="ops-dashboard__item-title"><a class="ops-dashboard__anchor" href="{{ $job['href'] }}">{{ $job['work_order_number'] }} · {{ $job['customer_name'] }}</a></div>
									<div class="ops-dashboard__item-subtitle">{{ $job['service_name'] }}</div>
									<div class="ops-dashboard__item-meta">{{ $job['scheduled_date'] }} · {{ $job['status'] }} · @if($job['invoice_href'])<a class="ops-dashboard__anchor" href="{{ $job['invoice_href'] }}">{{ $job['invoice_number'] }}</a>@else{{ $job['invoice_number'] ?: 'No invoice #' }}@endif</div>
								</div>
								<div class="ops-dashboard__item-value">{{ $this->formatMoney($job['amount']) }}</div>
							</div>
						@endforeach
					</div>
				@else
					<div class="ops-dashboard__empty">No upcoming jobs with invoiced revenue were found.</div>
				@endif
			</section>

			<section class="ops-dashboard__panel ops-dashboard__list-panel">
				<div class="ops-dashboard__section-header">
					<div>
						<p class="ops-dashboard__section-kicker">Material Spend</p>
						<h3 class="ops-dashboard__section-title">Cost Highlights</h3>
					</div>
					<p class="ops-dashboard__card-note">Selected reporting window</p>
				</div>

				<div class="ops-dashboard__material-highlights">
					@foreach ($this->materialHighlights as $highlight)
						<article class="ops-dashboard__metric">
							<p class="ops-dashboard__metric-label">{{ $highlight['label'] }}</p>
							<p class="ops-dashboard__metric-value">{{ $highlight['value'] }}</p>
							<p class="ops-dashboard__metric-meta">{{ $highlight['meta'] }}</p>
							@if (isset($highlight['width']))
								<div class="ops-dashboard__bar-track mt-3">
									<div class="ops-dashboard__bar-fill ops-dashboard__bar-fill--amber" style="width: {{ $highlight['width'] }}%"></div>
								</div>
							@endif
						</article>
					@endforeach
				</div>
			</section>
		</div>

		<div class="ops-dashboard__columns">
			<section class="ops-dashboard__panel ops-dashboard__list-panel">
				<div class="ops-dashboard__section-header">
					<div>
						<p class="ops-dashboard__section-kicker">Material Cost by Job</p>
						<h3 class="ops-dashboard__section-title">Highest Material Spend Jobs</h3>
					</div>
					<p class="ops-dashboard__card-note">Based on recorded work-order materials</p>
				</div>

				@if (count($this->topMaterialJobs))
					<div class="ops-dashboard__stack">
						@foreach ($this->topMaterialJobs as $job)
							<div class="ops-dashboard__bar-item">
								<div class="ops-dashboard__bar-meta">
									<div>
										<div class="ops-dashboard__item-title">{{ $job['work_order_number'] }} · {{ $job['customer_name'] }}</div>
										<div class="ops-dashboard__item-meta">{{ $job['service_name'] }} · {{ $this->formatCount($job['material_lines']) }} material lines · {{ $this->formatMoney($job['billable_cost']) }} billable</div>
									</div>
									<div class="ops-dashboard__item-value">{{ $this->formatMoney($job['total_cost']) }}</div>
								</div>
								<div class="ops-dashboard__bar-track">
									<div class="ops-dashboard__bar-fill ops-dashboard__bar-fill--amber" style="width: {{ $job['width'] }}%"></div>
								</div>
							</div>
						@endforeach
					</div>
				@else
					<div class="ops-dashboard__empty">No material costs were recorded inside the selected reporting range.</div>
				@endif
			</section>

			<div class="ops-dashboard__stack">
				<section class="ops-dashboard__panel ops-dashboard__list-panel">
					<div class="ops-dashboard__section-header">
						<div>
							<p class="ops-dashboard__section-kicker">Closeout Completeness</p>
							<h3 class="ops-dashboard__section-title">Completed Job Closeout</h3>
						</div>
						<p class="ops-dashboard__card-note">Photos, documents, and QA review coverage</p>
					</div>

					<div class="ops-dashboard__material-highlights">
						@foreach ($this->closeoutSummary as $summary)
							<a class="ops-dashboard__anchor" href="{{ $summary['href'] ?? '#' }}">
								<article class="ops-dashboard__metric">
									<p class="ops-dashboard__metric-label">{{ $summary['label'] }}</p>
									<p class="ops-dashboard__metric-value">{{ $summary['value'] }}</p>
									<p class="ops-dashboard__metric-meta">{{ $summary['meta'] }}</p>
								</article>
							</a>
						@endforeach
					</div>

					@if (count($this->closeoutItems))
						<div class="ops-dashboard__list mt-4">
							@foreach ($this->closeoutItems as $item)
								<div class="ops-dashboard__list-item">
									<div>
										<div class="ops-dashboard__item-title"><a class="ops-dashboard__anchor" href="{{ $item['href'] }}">{{ $item['work_order_number'] }} · {{ $item['customer_name'] }}</a></div>
										<div class="ops-dashboard__item-subtitle">{{ $item['service_name'] }}</div>
										<div class="ops-dashboard__item-meta">Completed {{ $item['completed_at'] }} · Missing {{ implode(', ', $item['missing']) }}</div>
									</div>
									<div class="ops-dashboard__item-value">{{ $item['photos'] }} photos · {{ $item['documents'] }} docs</div>
								</div>
							@endforeach
						</div>
					@else
						<div class="ops-dashboard__empty">All completed jobs in the selected range have closeout coverage.</div>
					@endif
				</section>

				<section class="ops-dashboard__panel ops-dashboard__list-panel">
					<div class="ops-dashboard__section-header">
						<div>
							<p class="ops-dashboard__section-kicker">Quality Risk</p>
							<h3 class="ops-dashboard__section-title">Current Rework Jobs</h3>
						</div>
					</div>

					@if (count($this->riskLists['rework']))
						<div class="ops-dashboard__list">
							@foreach ($this->riskLists['rework'] as $item)
								<div class="ops-dashboard__list-item">
									<div>
										<div class="ops-dashboard__item-title"><a class="ops-dashboard__anchor" href="{{ $item['href'] }}">{{ $item['title'] }}</a></div>
										<div class="ops-dashboard__item-subtitle">{{ $item['subtitle'] }}</div>
										<div class="ops-dashboard__item-meta">{{ $item['meta'] }}</div>
									</div>
									<div class="ops-dashboard__item-value">{{ $item['value'] }}</div>
								</div>
							@endforeach
						</div>
					@else
						<div class="ops-dashboard__empty">No work orders are currently sitting in rework.</div>
					@endif
				</section>

				<section class="ops-dashboard__panel ops-dashboard__list-panel">
					<div class="ops-dashboard__section-header">
						<div>
							<p class="ops-dashboard__section-kicker">Schedule Risk</p>
							<h3 class="ops-dashboard__section-title">Overdue Work Orders</h3>
						</div>
					</div>

					@if (count($this->riskLists['overdue']))
						<div class="ops-dashboard__list">
							@foreach ($this->riskLists['overdue'] as $item)
								<div class="ops-dashboard__list-item">
									<div>
										<div class="ops-dashboard__item-title"><a class="ops-dashboard__anchor" href="{{ $item['href'] }}">{{ $item['title'] }}</a></div>
										<div class="ops-dashboard__item-subtitle">{{ $item['subtitle'] }}</div>
										<div class="ops-dashboard__item-meta">Scheduled {{ $item['meta'] }}</div>
									</div>
									<div class="ops-dashboard__item-value">{{ $item['value'] }}</div>
								</div>
							@endforeach
						</div>
					@else
						<div class="ops-dashboard__empty">No active work orders are currently overdue.</div>
					@endif
				</section>
			</div>
		</div>

		<div class="ops-dashboard__split">
			<section class="ops-dashboard__panel ops-dashboard__list-panel">
				<div class="ops-dashboard__section-header">
					<div>
						<p class="ops-dashboard__section-kicker">Crew Load</p>
						<h3 class="ops-dashboard__section-title">Contractor Snapshot</h3>
					</div>
					<p class="ops-dashboard__card-note">Execution-state jobs only</p>
				</div>

				@if (count($this->contractorSnapshot))
					<div class="ops-dashboard__list">
						@foreach ($this->contractorSnapshot as $contractor)
							<div class="ops-dashboard__list-item">
								<div>
									<div class="ops-dashboard__item-title">{{ $contractor['name'] }}</div>
									<div class="ops-dashboard__item-meta">{{ $this->formatCount($contractor['in_progress_jobs']) }} in progress · {{ $this->formatCount($contractor['rework_jobs']) }} rework</div>
								</div>
								<div class="ops-dashboard__item-value">{{ $this->formatCount($contractor['active_jobs']) }} active</div>
							</div>
						@endforeach
					</div>
				@else
					<div class="ops-dashboard__empty">No contractor workload is currently active.</div>
				@endif
			</section>

			<section class="ops-dashboard__panel ops-dashboard__list-panel">
				<div class="ops-dashboard__section-header">
					<div>
						<p class="ops-dashboard__section-kicker">Recent Execution</p>
						<h3 class="ops-dashboard__section-title">Latest Work Orders</h3>
					</div>
					<p class="ops-dashboard__card-note">Sorted by most recent change</p>
				</div>

				@if (count($this->recentWorkOrders))
					<div class="ops-dashboard__table">
						@foreach ($this->recentWorkOrders as $job)
							<div class="ops-dashboard__table-row">
								<div>
									<div class="ops-dashboard__table-primary"><a class="ops-dashboard__anchor" href="{{ $job['href'] }}">{{ $job['work_order_number'] }} · {{ $job['customer_name'] }}</a></div>
									<div class="ops-dashboard__item-subtitle">{{ $job['service_name'] }}</div>
									<div class="ops-dashboard__item-meta">{{ $job['assigned_contractor'] }} · {{ $job['scheduled_date'] }} · {{ $job['updated_at'] }}</div>
								</div>
								<div class="ops-dashboard__table-secondary">
									<div><span class="ops-dashboard__pill">{{ $job['status'] }}</span></div>
									<div class="ops-dashboard__item-meta">@if($job['invoice_href'])<a class="ops-dashboard__anchor" href="{{ $job['invoice_href'] }}">{{ $job['invoice_number'] }}</a>@else{{ $job['invoice_number'] }}@endif</div>
								</div>
							</div>
						@endforeach
					</div>
				@else
					<div class="ops-dashboard__empty">No work orders have been updated recently.</div>
				@endif
			</section>
		</div>
	</div>
</x-filament-panels::page>
