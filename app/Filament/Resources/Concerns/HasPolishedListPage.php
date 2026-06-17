<?php

namespace App\Filament\Resources\Concerns;

trait HasPolishedListPage
{
    public function getHeroEyebrow(): string
    {
        return 'CenterThis admin';
    }

    public function getHeroTitle(): string
    {
        return (string) $this->getTitle();
    }

    public function getHeroDescription(): string
    {
        return 'Manage the operational data that powers the hire catalogue and booking workflow.';
    }

    /**
     * @return list<string>
     */
    public function getHeroBadges(): array
    {
        return [];
    }

    /**
     * @return list<array{label: string, value: string, description: string, tone: string}>
     */
    public function getHeroStats(): array
    {
        return [];
    }

    /**
     * @return array{label: string, value: string, description: string, tone: string}
     */
    protected function heroStat(string $label, string|int|float $value, string $description, string $tone = 'primary'): array
    {
        return [
            'label' => $label,
            'value' => is_float($value) ? number_format($value, 2) : (string) $value,
            'description' => $description,
            'tone' => $tone,
        ];
    }
}
