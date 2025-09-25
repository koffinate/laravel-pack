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
     * @return bool|null
     */
    private function isReturnJson(): bool|null
    {
        $forceToJson = config('koffinate.base.force_json', false);
        $forceToJsonPrefixes = collect((array) config('koffinate.base.force_json_prefixes', []));
        $forceToJsonPrefixes->each(fn ($it) => $forceToJsonPrefixes->add($it.'/*'));

        return is_bool($forceToJson) && $forceToJson && $this->is($forceToJsonPrefixes->toArray()) || null;
    }
}
