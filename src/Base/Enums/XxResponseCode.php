<?php

namespace Kfn\Base\Enums;

use Kfn\Base\Enum;
use Symfony\Component\HttpFoundation\Response;

class XxResponseCode extends Enum
{
    const string SUCCESS = 'SUCCESS';
    const string CREATED = 'CREATED';
    const string ERR_VALIDATION = 'ERR_VALIDATION';
    const string ERR_AUTHENTICATION = 'ERR_AUTHENTICATION';
    const string ERR_INVALID_IP_ADDRESS = 'ERR_INVALID_IP_ADDRESS';
    const string ERR_MISSING_SIGNATURE_HEADER = 'ERR_MISSING_SIGNATURE_HEADER';
    const string ERR_INVALID_SIGNATURE_HEADER = 'ERR_INVALID_SIGNATURE_HEADER';
    const string ERR_INVALID_OPERATION = 'ERR_INVALID_OPERATION';
    const string ERR_ENTITY_NOT_FOUND = 'ERR_ENTITY_NOT_FOUND';
    const string ERR_ROUTE_NOT_FOUND = 'ERR_ROUTE_NOT_FOUND';
    const string ERR_UNKNOWN = 'ERR_UNKNOWN';
    const string ERR_FORBIDDEN_ACCESS = 'ERR_FORBIDDEN_ACCESS';
    const string ERR_METHOD_NOT_IMPLEMENTED = 'ERR_METHOD_NOT_IMPLEMENTED';
    const string ERR_ACTION_UNAUTHORIZED = 'ERR_ACTION_UNAUTHORIZED';

    /**
     * Determine httpCode from response code.
     *
     * @return int
     */
    public function httpCode(): int
    {
        return match ($this->value) {
            static::SUCCESS => Response::HTTP_OK,
            static::CREATED => Response::HTTP_CREATED,
            static::ERR_MISSING_SIGNATURE_HEADER,
            static::ERR_INVALID_SIGNATURE_HEADER,
            static::ERR_INVALID_IP_ADDRESS,
            static::ERR_AUTHENTICATION => Response::HTTP_UNAUTHORIZED,
            static::ERR_VALIDATION => Response::HTTP_UNPROCESSABLE_ENTITY,
            static::ERR_INVALID_OPERATION => Response::HTTP_EXPECTATION_FAILED,
            static::ERR_ENTITY_NOT_FOUND,
            static::ERR_ROUTE_NOT_FOUND => Response::HTTP_NOT_FOUND,
            static::ERR_UNKNOWN,
            static::ERR_METHOD_NOT_IMPLEMENTED => Response::HTTP_INTERNAL_SERVER_ERROR,
            static::ERR_FORBIDDEN_ACCESS,
            static::ERR_ACTION_UNAUTHORIZED => Response::HTTP_FORBIDDEN,

            default => Response::HTTP_BAD_REQUEST
        };
    }

    /**
     * Set error to readable message string.
     *
     * @return string
     */
    public function message(): string
    {
        return ucwords(strtolower(str_replace(['ERR_', '_'], ['', ' '], $this->name)));
    }
}
