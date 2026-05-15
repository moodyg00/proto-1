<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\Service;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class CatalogPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'Accounting';

    protected static ?string $navigationLabel = 'Catalog';

    protected static ?int $navigationSort = 8;

    protected static ?string $slug = 'catalog';

    protected static string $view = 'filament.pages.catalog-page';

    public function getServices(): array
    {
        return Service::query()
            ->orderBy('category')
            ->orderBy('name')
            ->limit(12)
            ->get(['name', 'category', 'suggested_price', 'is_active'])
            ->map(fn (Service $service): array => [
                'name' => $service->name,
                'category' => $service->category,
                'price' => (float) ($service->suggested_price ?? 0),
                'active' => (bool) $service->is_active,
            ])
            ->all();
    }

    public function getSellableProducts(): array
    {
        return $this->getProductsQuery()
            ->where('is_for_sale', true)
            ->get()
            ->map(fn (Product $product): array => [
                'name' => $product->name,
                'category' => $product->category,
                'price' => (float) ($product->unit_price ?? 0),
                'sku' => $product->sku,
            ])
            ->all();
    }

    public function getInternalProducts(): array
    {
        return $this->getProductsQuery()
            ->where('is_internal_use', true)
            ->get()
            ->map(fn (Product $product): array => [
                'name' => $product->name,
                'category' => $product->category,
                'price' => (float) ($product->unit_price ?? 0),
                'sku' => $product->sku,
            ])
            ->all();
    }

    public function getRefurbishedProducts(): array
    {
        return $this->getProductsQuery()
            ->where('category', 'ilike', '%refurb%')
            ->get()
            ->map(fn (Product $product): array => [
                'name' => $product->name,
                'category' => $product->category,
                'price' => (float) ($product->unit_price ?? 0),
                'sku' => $product->sku,
            ])
            ->all();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('newService')
                ->label('Add Service')
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('primary')
                ->form([
                    Forms\Components\TextInput::make('name')->required()->maxLength(255),
                    Forms\Components\TextInput::make('category')->maxLength(120),
                    Forms\Components\TextInput::make('suggested_price')->numeric()->prefix('$'),
                    Forms\Components\Textarea::make('description')->rows(3)->columnSpanFull(),
                    Forms\Components\Toggle::make('is_active')->default(true),
                ])
                ->action(function (array $data): void {
                    Service::query()->create($data + [
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id(),
                    ]);

                    Notification::make()
                        ->title('Service added to catalog')
                        ->success()
                        ->send();
                }),
            Actions\Action::make('newProduct')
                ->label('Add Product')
                ->icon('heroicon-o-cube')
                ->color('gray')
                ->form([
                    Forms\Components\TextInput::make('name')->required()->maxLength(255),
                    Forms\Components\TextInput::make('category')->maxLength(120),
                    Forms\Components\TextInput::make('sku')->maxLength(120),
                    Forms\Components\TextInput::make('unit_price')->numeric()->prefix('$'),
                    Forms\Components\TextInput::make('unit_of_measure')->maxLength(60),
                    Forms\Components\Textarea::make('description')->rows(3)->columnSpanFull(),
                    Forms\Components\Toggle::make('is_for_sale')->label('Sellable')->default(true),
                    Forms\Components\Toggle::make('is_internal_use')->label('Internal use')->default(false),
                ])
                ->action(function (array $data): void {
                    Product::query()->create($data + [
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id(),
                    ]);

                    Notification::make()
                        ->title('Product added to catalog')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getProductsQuery()
    {
        return Product::query()
            ->orderBy('category')
            ->orderBy('name')
            ->limit(12);
    }
}