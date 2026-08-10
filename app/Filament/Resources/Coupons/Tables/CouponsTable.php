<?php

namespace App\Filament\Resources\Coupons\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Columns\BadgeColumn;
   
class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('coupon_code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('coupon_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('coupon_amount')
                    ->numeric()
                    ->sortable(),
                BadgeColumn::make('coupon_type')
                    ->label('Type')
                    ->formatStateUsing(fn ($state) => $state == 0 ? 'Cash' : 'Percentage')
                    ->colors([
                        'success' => fn ($state) => $state == 0,
                        'warning' => fn ($state) => $state == 1,
                    ]),
                TextColumn::make('minimum_order_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('redemptions_count')
                    ->label('Redemptions')
                    ->state(fn ($record) => $record->usages()->count()),
                TextColumn::make('remaining_uses')
                    ->label('Remaining Uses')
                    ->state(fn ($record) => $record->global_usage_limit > 0 ? max(0, $record->global_usage_limit - $record->usages()->count()) : 'Unlimited'),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->state(function ($record) {
                        if (isset($record->active) && !$record->active) return 'Disabled';
                        $today = \Illuminate\Support\Carbon::today()->toDateString();
                        if ($record->coupon_fromdate && $record->coupon_fromdate > $today) return 'Scheduled';
                        if ($record->coupon_todate && $record->coupon_todate < $today) return 'Expired';
                        return 'Active';
                    })
                    ->colors([
                        'success' => 'Active',
                        'info' => 'Scheduled',
                        'danger' => 'Expired',
                        'secondary' => 'Disabled',
                    ]),
                TextColumn::make('coupon_fromdate')
                    ->date()
                    ->sortable() 
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('coupon_todate')
                    ->date()
                    ->sortable() 
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
