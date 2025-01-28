<?php

namespace Kfn\Util\Enums;

use Exception;

enum Spacer: int
{
    case NORMAL = 4;
    case THIN = 2;
    case WIDE = 8;

    /**
     * @return string
     * @throws \Throwable
     */
    public function space(): string
    {
        try {
            return match ($this) {
                self::NORMAL => '&nbsp;&nbsp;&nbsp;&nbsp;',
                self::THIN => '&nbsp;&nbsp;',
                self::WIDE => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
            };
        } catch (Exception $e) {
            if(app()->hasDebugModeEnabled()) {
                throw $e;
            }
        }

        return '';
    }

    /**
     * @return string
     * @throws \Throwable
     */
    public function dot(): string
    {
        try {
            return match ($this) {
                self::NORMAL => '.. ..',
                self::THIN => '..',
                self::WIDE => '.. .. .. ..',
            };
        } catch (Exception $e) {
            if(app()->hasDebugModeEnabled()) {
                throw $e;
            }
        }

        return '';
    }

    /**
     * @return string
     * @throws \Throwable
     */
    public function arrow(): string
    {
        try {
            return match ($this) {
                self::NORMAL => '--->',
                self::THIN => '->',
                self::WIDE => '------->',
            };
        } catch (Exception $e) {
            if(app()->hasDebugModeEnabled()) {
                throw $e;
            }
        }

        return '';
    }
}
