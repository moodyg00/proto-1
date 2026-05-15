<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountingReportResource\Pages;
use App\Models\AccountingReport;
use App\Services\AccountingReportService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class AccountingReportResource extends Resource
{
    protected static ?string $model = AccountingReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationGroup = 'Accounting';

    protected static ?string $navigationLabel = 'Reports';

    protected static ?int $navigationSort = 11;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('report_type')
                    ->options(AccountingReportService::typeOptions())
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('filters.start_date')
                    ->label('Start date')
                    ->default(now()->startOfMonth()),
                Forms\Components\DatePicker::make('filters.end_date')
                    ->label('End date')
                    ->default(now()->endOfMonth()),
                Forms\Components\TagsInput::make('email_recipients')
                    ->placeholder('finance@example.com')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('report_type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => AccountingReportService::typeOptions()[$state] ?? str($state)->headline()->toString()),
                Tables\Columns\TextColumn::make('filters.start_date')
                    ->label('From')
                    ->date(),
                Tables\Columns\TextColumn::make('filters.end_date')
                    ->label('To')
                    ->date(),
                Tables\Columns\TextColumn::make('last_generated_at')
                    ->label('Last Export')
                    ->since()
                    ->placeholder('Not exported'),
                Tables\Columns\TextColumn::make('email_recipients')
                    ->badge()
                    ->separator(','),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('report_type')
                    ->options(AccountingReportService::typeOptions()),
            ])
            ->actions([
                Tables\Actions\Action::make('exportPdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function (AccountingReport $record, AccountingReportService $reportService) {
                        $path = $reportService->exportPdf($record);

                        return response()->download(Storage::disk('public')->path($path));
                    }),
                Tables\Actions\Action::make('attachToEmail')
                    ->label('Attach to Email')
                    ->icon('heroicon-o-envelope')
                    ->color('primary')
                    ->form([
                        Forms\Components\TextInput::make('recipient')
                            ->email()
                            ->required()
                            ->default(fn (AccountingReport $record): ?string => $record->email_recipients[0] ?? null),
                        Forms\Components\TextInput::make('subject')
                            ->required()
                            ->default(fn (AccountingReport $record): string => $record->name . ' PDF Report'),
                        Forms\Components\Textarea::make('message')
                            ->rows(4)
                            ->default('Attached is your requested accounting report.')
                            ->columnSpanFull(),
                    ])
                    ->action(function (AccountingReport $record, array $data, AccountingReportService $reportService): void {
                        $reportService->emailReport($record, $data['recipient'], $data['subject'], $data['message'] ?: null);

                        Notification::make()
                            ->title('Report emailed')
                            ->body('The PDF export was attached and sent to ' . $data['recipient'] . '.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAccountingReports::route('/'),
        ];
    }
}
