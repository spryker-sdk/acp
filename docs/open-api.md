# OpenApi

The following console commands are available:

- `vendor/bin/acp openapi:create`

## Adding an OpenAPI file

The `vendor/bin/acp openapi:create` adds a minimal OpenAPI file.

### Arguments and Options

#### Arguments

- `title`

`vendor/bin/acp openapi:create "Your Open API title"` will set the title in your OpenAPI file.

```
...
info:
    title: 'Your Open API title'
...
```

#### Options

- `openapi-file`
- `api-version`

`vendor/bin/acp openapi:create --openapi-file "path/to/open-api.yml"` will override the default file location (config/api/openapi/openapi.yml).


After the command was running you need to generate the transfer objects `vendor/bin/console transfer:generate`.
