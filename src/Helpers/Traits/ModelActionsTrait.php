<?php

namespace RepeatToolkit\Helpers\Traits;

trait ModelActionsTrait
{
    public function modelActions(): array
    {
        $actions = [];

      /*  if (method_exists($this, 'canEdit') ? $this->canEdit() : true) {
            $actions[] = [
                'label' => 'Edit',
                'type' => 'edit',
                'url' => route($this->getRoutePrefix() . '.edit', $this->id),
            ];
        }*/

        if (method_exists($this, 'canDelete') ? $this->canDelete() : true) {
            $actions[] = [
                'label' => __i("Brisanje"),
                'type' => 'delete',
                'url' => route($this->getRoutePrefix() . '.get_delete', $this->id),
                'method' => 'DELETE',
                'confirm' => true,
            ];
        }

        // Optional custom model-specific actions
        if (method_exists($this, 'customActions')) {
            $actions = array_merge($actions, $this->customActions());
        }

        return $actions;
    }

    protected function getRoutePrefix(): string
    {
        // Default assumes route names like `users.edit`
        return str($this->getTable())->kebab();
    }
}
