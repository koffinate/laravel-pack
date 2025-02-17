<?php

namespace Kfn\UI\Concerns;

trait InteractWithUILink
{
    /**
     * @param  string  $name
     * @param  mixed  $value
     *
     * @return void
     */
    private function setControllerButton(string $name, mixed $value): void
    {
        // if (method_exists($this, 'setData')) {
        //     $this->setData($name, $value);
        // }
        if (! view()->shared($name)) {
            view()->share($name, $value);
        }
    }

    /**
     * Load default controller buttons
     *
     * @return void
     */
    private function loadControllerButtons(): void
    {
        $this->setBackLink('#');
        $this->setDeleteLink('#');
        $this->setDetailLink('#');
        $this->setEditLink('#');
        $this->setSaveLink('#');
    }

    /**
     * Set Back Link.
     *
     * @param  string  $link
     *
     * @return static
     */
    protected function setBackLink(string $link): static
    {
        $this->setControllerButton('backLink', $link);
        return $this;
    }

    /**
     * Set Detail Link.
     *
     * @param  string  $link
     *
     * @return static
     */
    protected function setDetailLink(string $link): static
    {
        $this->setControllerButton('detailLink', $link);
        return $this;
    }

    /**
     * Set Delete Link.
     *
     * @param  string  $link
     *
     * @return static
     */
    protected function setDeleteLink(string $link): static
    {
        $this->setControllerButton('deleteLink', $link);
        return $this;
    }

    /**
     * Set Save Link.
     *
     * @param  string  $link
     *
     * @return static
     */
    protected function setSaveLink(string $link): static
    {
        $this->setControllerButton('saveLink', $link);
        return $this;
    }

    /**
     * Set Edit Link.
     *
     * @param  string  $link
     *
     * @return static
     */
    protected function setEditLink(string $link): static
    {
        $this->setControllerButton('editLink', $link);
        return $this;
    }
}
