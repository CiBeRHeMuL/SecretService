<?php

namespace App\Presentation\Api\Enum;

enum ErrorSlugEnum: string
{
    // 3xx HTTP codes
    case MultipleChoices = 'MULTIPLE_CHOICES';
    case MovedPermanently = 'MOVED_PERMANENTLY';
    case Found = 'FOUND';
    case SeeOther = 'SEE_OTHER';
    case NotModified = 'NOT_MODIFIED';
    case UseProxy = 'USE_PROXY';
    case SwitchProxy = 'SWITCH_PROXY';
    case TemporaryRedirect = 'TEMPORARY_REDIRECT';
    case PermanentRedirect = 'PERMANENT_REDIRECT';

    // 4xx HTTP codes
    case BadRequest = 'BAD_REQUEST';
    case Unauthorized = 'UNAUTHORIZED';
    case PaymentRequired = 'PAYMENT_REQUIRED';
    case Forbidden = 'FORBIDDEN';
    case NotFound = 'NOT_FOUND';
    case MethodNotAllowed = 'METHOD_NOT_ALLOWED';
    case NotAcceptable = 'NOT_ACCEPTABLE';
    case ProxyAuthenticationRequired = 'PROXY_AUTHENTICATION_REQUIRED';
    case RequestTimeout = 'REQUEST_TIMEOUT';
    case Conflict = 'CONFLICT';
    case Gone = 'GONE';
    case LengthRequired = 'LENGTH_REQUIRED';
    case PreconditionFailed = 'PRECONDITION_FAILED';
    case PayloadTooLarge = 'PAYLOAD_TOO_LARGE';
    case UriTooLong = 'URI_TOO_LONG';
    case UnsupportedMediaType = 'UNSUPPORTED_MEDIA_TYPE';
    case RangeNotSatisfiable = 'RANGE_NOT_SATISFIABLE';
    case ExpectationFailed = 'EXPECTATION_FAILED';
    case ImATeapot = 'IM_A_TEAPOT';
    case MisdirectedRequest = 'MISDIRECTED_REQUEST';
    case UnprocessableEntity = 'UNPROCESSABLE_ENTITY';
    case Locked = 'LOCKED';
    case FailedDependency = 'FAILED_DEPENDENCY';
    case TooEarly = 'TOO_EARLY';
    case UpgradeRequired = 'UPGRADE_REQUIRED';
    case PreconditionRequired = 'PRECONDITION_REQUIRED';
    case TooManyRequests = 'TOO_MANY_REQUESTS';
    case RequestHeaderFieldsTooLarge = 'REQUEST_HEADER_FIELDS_TOO_LARGE';
    case UnavailableForLegalReasons = 'UNAVAILABLE_FOR_LEGAL_REASONS';

    // 5xx HTTP codes
    case InternalServerError = 'INTERNAL_SERVER_ERROR';
    case NotImplemented = 'NOT_IMPLEMENTED';
    case BadGateway = 'BAD_GATEWAY';
    case ServiceUnavailable = 'SERVICE_UNAVAILABLE';
    case GatewayTimeout = 'GATEWAY_TIMEOUT';
    case HttpVersionNotSupported = 'HTTP_VERSION_NOT_SUPPORTED';
    case VariantAlsoNegotiates = 'VARIANT_ALSO_NEGOTIATES';
    case InsufficientStorage = 'INSUFFICIENT_STORAGE';
    case LoopDetected = 'LOOP_DETECTED';
    case NotExtended = 'NOT_EXTENDED';
    case NetworkAuthenticationRequired = 'NETWORK_AUTHENTICATION_REQUIRED';

    // Validation slugs
    case EmptyField = 'EMPTY_FIELD';
    case WrongField = 'WRONG_FIELD';
    case UserExists = 'USER_EXISTS';
    case TokenExpired = 'TOKEN_EXPIRED';

    public function getSlug(): string
    {
        return $this->value;
    }

    public static function fromCode(int $code): ErrorSlugEnum
    {
        return match ($code) {
            // 2xx codes
            300 => self::MultipleChoices,
            301 => self::MovedPermanently,
            302 => self::Found,
            303 => self::SeeOther,
            304 => self::NotModified,
            305 => self::UseProxy,
            306 => self::SwitchProxy,
            307 => self::TemporaryRedirect,
            308 => self::PermanentRedirect,
            // 4xx codes
            400 => self::BadRequest,
            401 => self::Unauthorized,
            402 => self::PaymentRequired,
            403 => self::Forbidden,
            404 => self::NotFound,
            405 => self::MethodNotAllowed,
            406 => self::NotAcceptable,
            407 => self::ProxyAuthenticationRequired,
            408 => self::RequestTimeout,
            409 => self::Conflict,
            410 => self::Gone,
            411 => self::LengthRequired,
            412 => self::PreconditionFailed,
            413 => self::PayloadTooLarge,
            414 => self::UriTooLong,
            415 => self::UnsupportedMediaType,
            416 => self::RangeNotSatisfiable,
            417 => self::ExpectationFailed,
            418 => self::ImATeapot,
            421 => self::MisdirectedRequest,
            422 => self::UnprocessableEntity,
            423 => self::Locked,
            424 => self::FailedDependency,
            425 => self::TooEarly,
            426 => self::UpgradeRequired,
            428 => self::PreconditionRequired,
            429 => self::TooManyRequests,
            431 => self::RequestHeaderFieldsTooLarge,
            451 => self::UnavailableForLegalReasons,
            // 5xx  codes
            500 => self::InternalServerError,
            501 => self::NotImplemented,
            502 => self::BadGateway,
            503 => self::ServiceUnavailable,
            504 => self::GatewayTimeout,
            505 => self::HttpVersionNotSupported,
            506 => self::VariantAlsoNegotiates,
            507 => self::InsufficientStorage,
            508 => self::LoopDetected,
            510 => self::NotExtended,
            511 => self::NetworkAuthenticationRequired,
        };
    }
}
