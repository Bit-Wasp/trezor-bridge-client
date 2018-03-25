<?php

declare(strict_types=1);

namespace BitWasp\Trezor\Bridge\Schema;

class ValidatorFactory
{
    const VERSION_RESPONSE_VALIDATOR = <<<JSON
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
JSON;

    const LIST_DEVICES_RESPONSE_VALIDATOR = <<<JSON
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
JSON;

    const ACQUIRE_RESPONSE_VALIDATOR = <<<JSON
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
JSON;

    const RELEASE_RESPONSE_VALIDATOR = <<<JSON
{
  "title": "ReleaseResponse",
  "type": "object",  
  "properties": {},
  "required": []
}
JSON;

    const CALL_RESPONSE_VALIDATOR = <<<JSON
{
  "title": "CallResponse",
  "type": "object",  
  "properties": {
    "type": {
      "type": "string"
    },
    "body": {
      "type": "string"
    }
  },
  "required": [
    "type", "body"
  ]
}
JSON;

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

    public function callResponse(): \stdClass
    {
        return json_decode(self::CALL_RESPONSE_VALIDATOR);
    }
}
