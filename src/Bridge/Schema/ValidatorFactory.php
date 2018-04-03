<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Bridge\Schema;

class ValidatorFactory
{
    const VERSION_RESPONSE_VALIDATOR = <<<EOJSON
{
    "title": "VersionResponse",
    "type": "object",
    "properties": {
        "version": {
            "type": "string"
        }
    },
    "required": ["version"]
}
EOJSON;

    const LIST_DEVICES_RESPONSE_VALIDATOR = <<<EOJSON
{
  "title": "ListDevicesResponse",
  "type": "array",
  "items": {
    "type": "object",
    "properties": {
      "path": {
        "type": "string"
      },
      "vendor": {
        "type": "number"
      },
      "product": {
        "type": "number"
      },
      "session": {
        "type": ["string", "null"]
      }
    },
    "required": [
      "path", "session"
    ]
  }
}
EOJSON;

    const ACQUIRE_RESPONSE_VALIDATOR = <<<EOJSON
{
  "title": "AcquireResponse",
  "type": "object",  
  "properties": {
    "session": {
      "type": "string"
    }
  },
  "required": [
    "session"
  ]
}
EOJSON;

    const RELEASE_RESPONSE_VALIDATOR = <<<EOJSON
{
  "title": "ReleaseResponse",
  "type": "object",  
  "properties": {},
  "required": []
}
EOJSON;

    public function versionResponse(): \stdClass
    {
        return json_decode(self::VERSION_RESPONSE_VALIDATOR);
    }

    public function listDevicesResponse(): \stdClass
    {
        return json_decode(self::LIST_DEVICES_RESPONSE_VALIDATOR);
    }

    public function acquireResponse(): \stdClass
    {
        return json_decode(self::ACQUIRE_RESPONSE_VALIDATOR);
    }

    public function releaseResponse(): \stdClass
    {
        return json_decode(self::RELEASE_RESPONSE_VALIDATOR);
    }
}
