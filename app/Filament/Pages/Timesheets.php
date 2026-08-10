<?php

namespace App\Filament\Pages;

use App\Enums\TaskType;
use App\Models\Timesheet;
use App\Models\User;
use Filament\Pages\Page;
use BackedEnum;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Radio;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;

class Timesheets extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static ?string $title = 'Timesheets';

    protected static ?string $navigationLabel = 'Timesheets';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.timesheets';

    /**
     * Helper to format time range.
     */
    public static function formatTimeRange(Timesheet $record): string
    {
        $start = $record->start_time->format('g:i A');
        
        if (empty($record->end_time)) {
            return "{$start} - Running";
        }

        $end = $record->end_time->format('g:i A');
        return "{$start} - {$end}";
    }

    /**
     * Helper to calculate duration.
     */
    public static function calculateDuration(Timesheet $record): string
    {
        if (empty($record->end_time)) {
            return 'Running';
        }

        $start = $record->start_time;
        $end = $record->end_time;

        // Handle overnight crossing if any
        if ($end->lt($start)) {
            $end = $end->addDay();
        }

        $diffMinutes = $start->diffInMinutes($end);

        if ($diffMinutes < 60) {
            $minuteLabel = $diffMinutes === 1 ? 'Minute' : 'Minutes';
            return "{$diffMinutes} {$minuteLabel}";
        }

        $hours = $diffMinutes / 60;
        $roundedHours = round($hours, 1);

        if (fmod($hours, 1) === 0.0) {
            $hourLabel = $hours === 1 ? 'hour' : 'hours';
            return "{$hours} {$hourLabel}";
        }

        $hourLabel = $roundedHours === 1.0 ? 'hour' : 'hours';
        return "{$roundedHours} {$hourLabel}";
    }

    /**
     * Get staff users for dropdown.
     */
    protected function getStaffUsers(): array
    {
        return User::role('Staff')
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * Get task options from enum.
     */
    protected function getTaskOptions(): array
    {
        $options = [];
        foreach (TaskType::cases() as $task) {
            $options[$task->value] = $task->getLabel();
        }
        return $options;
    }

    /**
     * Parse date + time from form inputs robustly to avoid format errors.
     */
    protected function parseDateTimeFromForm(?string $date, $time): ?Carbon
    {
        if (empty($date) || empty($time)) {
            return null;
        }

        $datetime = $date . ' ' . $time;

        $formats = ['Y-m-d H:i:s', 'Y-m-d H:i'];
        foreach ($formats as $fmt) {
            try {
                return Carbon::createFromFormat($fmt, $datetime);
            } catch (\Exception $e) {
                // try next
            }
        }

        try {
            return Carbon::parse($datetime);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Timesheet::query())
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('date')
                    ->label('Date')
                    ->date('d-m-Y'),
                TextColumn::make('staffUser.name')
                    ->label('Staff')
                    ->badge()
                    ->color('info'),
                TextColumn::make('task')
                    ->label('Task')
                    ->weight('bold')
                    ->formatStateUsing(fn ($state) => $state instanceof TaskType ? $state->getLabel() : TaskType::from($state)->getLabel()),
                TextColumn::make('description')
                    ->label('Description')
                    ->limit(50),
                TextColumn::make('time_range')
                    ->label('Time')
                    ->state(fn (Timesheet $record) => static::formatTimeRange($record)),
                TextColumn::make('duration')
                    ->label('Duration')
                    ->badge()
                    ->color(fn (Timesheet $record) => empty($record->end_time) ? 'warning' : 'success')
                    ->state(fn (Timesheet $record) => static::calculateDuration($record)),
            ])
            ->headerActions([
                // Add Timesheet Action
                Action::make('add')
                    ->label('Add Timesheet')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Add Timesheet Manually / Timer')
                    ->modalWidth('lg')
                    ->form([
                        Select::make('staff_user_id')
                            ->label('Select Staff Member')
                            ->options($this->getStaffUsers())
                            ->required(),
                        Select::make('task')
                            ->label('Select Task')
                            ->options($this->getTaskOptions())
                            ->required(),
                        Textarea::make('description')
                            ->label('Task Description')
                            ->placeholder('Describe the task performed...')
                            ->required(),
                        Radio::make('entry_mode')
                            ->label('Time Entry Mode')
                            ->options([
                                'timer' => 'Start Timer Now (Task Runs)',
                                'manual' => 'Enter Date & Time Manually',
                            ])
                            ->default('manual')
                            ->live(),
                        DatePicker::make('date')
                            ->label('Date')
                            ->default(now()->toDateString())
                            ->visible(fn (callable $get) => $get('entry_mode') === 'manual')
                            ->required(fn (callable $get) => $get('entry_mode') === 'manual'),
                        TimePicker::make('start_time')
                            ->label('Start Time')
                            ->default(now()->format('H:i'))
                            ->visible(fn (callable $get) => $get('entry_mode') === 'manual')
                            ->required(fn (callable $get) => $get('entry_mode') === 'manual'),
                        TimePicker::make('end_time')
                            ->label('End Time (Optional)')
                            ->visible(fn (callable $get) => $get('entry_mode') === 'manual'),
                    ])
                    ->action(function (array $data) {
                        if ($data['entry_mode'] === 'timer') {
                            Timesheet::create([
                                'staff_user_id' => $data['staff_user_id'],
                                'task' => $data['task'],
                                'description' => $data['description'],
                                'date' => now()->toDateString(),
                                'start_time' => now(),
                                'end_time' => null,
                            ]);
                        } else {
                            Timesheet::create([
                                'staff_user_id' => $data['staff_user_id'],
                                'task' => $data['task'],
                                'description' => $data['description'],
                                'date' => $data['date'],
                                'start_time' => $this->parseDateTimeFromForm($data['date'], $data['start_time']),
                                'end_time' => $this->parseDateTimeFromForm($data['date'], $data['end_time']),
                            ]);
                        }

                        Notification::make()
                            ->title($data['entry_mode'] === 'timer' ? 'Timer started!' : 'Timesheet entry added!')
                            ->success()
                            ->send();
                    }),

                // Export Action (for future use in phase 2)
                Action::make('export')
                    ->label('Export')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function () {
                        Notification::make()
                            ->title('Timesheets exported successfully! (Coming in Phase 2)')
                            ->info()
                            ->send();
                    }),
            ])
            ->actions([
                // Stop Timer Action
                Action::make('stop_timer')
                    ->label('Stop')
                    ->icon('heroicon-o-stop')
                    ->color('danger')
                    ->button()
                    ->visible(fn (Timesheet $record) => empty($record->end_time))
                    ->action(function (Timesheet $record) {
                        $record->update([
                            'end_time' => now(),
                        ]);

                        Notification::make()
                            ->title('Task timer stopped!')
                            ->success()
                            ->send();
                    }),

                // Edit Action
                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->modalHeading('Edit Timesheet')
                    ->modalWidth('lg')
                    ->fillForm(fn (Timesheet $record): array => [
                        'staff_user_id' => $record->staff_user_id,
                        'task' => $record->task->value,
                        'description' => $record->description,
                        'date' => $record->date->toDateString(),
                        'start_time' => $record->start_time->format('H:i'),
                        'end_time' => $record->end_time ? $record->end_time->format('H:i') : null,
                    ])
                    ->form([
                        Select::make('staff_user_id')
                            ->label('Select Staff Member')
                            ->options($this->getStaffUsers())
                            ->required(),
                        Select::make('task')
                            ->label('Select Task')
                            ->options($this->getTaskOptions())
                            ->required(),
                        Textarea::make('description')
                            ->label('Task Description')
                            ->required(),
                        DatePicker::make('date')
                            ->label('Date')
                            ->required(),
                        TimePicker::make('start_time')
                            ->label('Start Time')
                            ->required(),
                        TimePicker::make('end_time')
                            ->label('End Time (Optional)'),
                    ])
                    ->action(function (Timesheet $record, array $data) {
                        $record->update([
                            'staff_user_id' => $data['staff_user_id'],
                            'task' => $data['task'],
                            'description' => $data['description'],
                            'date' => $data['date'],
                            'start_time' => $this->parseDateTimeFromForm($data['date'], $data['start_time']),
                            'end_time' => $this->parseDateTimeFromForm($data['date'], $data['end_time']),
                        ]);

                        Notification::make()
                            ->title('Timesheet updated!')
                            ->success()
                            ->send();
                    }),

                // Delete Action
                Action::make('delete')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Timesheet $record) {
                        $record->delete();

                        Notification::make()
                            ->title('Timesheet deleted!')
                            ->success()
                            ->send();
                    }),
            ])
            ->filters([
                Filter::make('date')
                    ->form([
                        DatePicker::make('date')
                            ->label('Filter By Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['date']) {
                            return null;
                        }
                        return 'Date: ' . Carbon::parse($data['date'])->format('d-m-Y');
                    }),

                Filter::make('staff_user_id')
                    ->form([
                        Select::make('staff_user_id')
                            ->label('Filter By Staff')
                            ->options($this->getStaffUsers()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['staff_user_id'],
                                fn (Builder $query, $staffId): Builder => $query->where('staff_user_id', $staffId),
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['staff_user_id']) {
                            return null;
                        }
                        $staffName = User::find($data['staff_user_id'])?->name;
                        return 'Staff: ' . $staffName;
                    }),
            ]);
    }
}
