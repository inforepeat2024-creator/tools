<?php

namespace RepeatToolkit\Helpers\Traits;

trait ModelActionsTrait
{
    public function modelActions(): array
    {
        $actions = [];

        if (method_exists($this, 'canEdit') ? $this->canEdit() : true) {
            $actions[] = [
                'label' => __i("Izmeni"),
                'type' => 'edit',
                'url' => route($this->getRoutePrefix() . '.create_partial', ['basic', $this->id]),
            ];
            // Optional custom model-specific actions inserted right after edit
            if (method_exists($this, 'customActions')) {
                $actions = array_merge($actions, $this->customActions());
            }
        }

        if (method_exists($this, 'canDelete') ? $this->canDelete() : true) {
            $actions[] = [
                'label' => __i("Obriši"),
                'type' => 'delete',
                'url' => route($this->getRoutePrefix() . '.get_delete', $this->id),
                'method' => 'GET',
                'confirm' => true,
            ];
        }

        return $actions;
    }

    protected function getRoutePrefix(): string
    {
        // Default assumes route names like `users.edit`
        return str($this->getTable())->kebab();
    }
}
