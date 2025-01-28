<?php

namespace Kfn\Base;

class Request extends \Illuminate\Http\Request
{
    /** @inheritDoc */
    public function expectsJson(): bool
    {
        return $this->isReturnJson() ?? parent::expectsJson();
    }

    /** @inheritDoc */
    public function wantsJson(): bool
    {
        return $this->isReturnJson() ?? parent::wantsJson();
    }

    /**
     * @return true|null
     */
    private function isReturnJson(): true|null
    {
        $forceToJson = config('koffinate.base.force_json', false);
        if (
            is_bool($forceToJson) && $forceToJson &&
            in_array($this->segment(1), (array) config('koffinate.base.force_json_prefixes', []))
        ) {
            return true;
        }
        return null;
    }
}
