
# Bundle Configuration

Created via `./bin/console config:dump-reference dbp_relay_blob_connector_campusonline_dms | sed '/^$/d'`

```yaml
# Default configuration for extension with alias: "dbp_relay_blob_connector_campusonline_dms"
dbp_relay_blob_connector_campusonline_dms:
    authorization:
        roles:
            # Returns true if the current user is authorized to use the API
            ROLE_USER:            'false'
        resource_permissions: []
        attributes:           []
    blob_library:
        # Whether to use the HTTP mode, i.e. the Blob HTTP (REST) API. If false, a custom Blob API implementation will be used.
        use_http_mode:        true
        # The fully qualified name or alias of the service to use as custom Blob API implementation. Default is the PHP Blob File API, which comes with the Relay Blob bundle and talks to Blob directly over PHP.
        custom_file_api_service: dbp.relay.blob.file_api
        # The identifier of the Blob bucket
        bucket_identifier:    ~ # Required
        http_mode:
            # The signature key of the Blob bucket. Required for HTTP mode.
            bucket_key:           ~
            # The base URL of the HTTP Blob API. Required for HTTP mode.
            blob_base_url:        ~
            # Whether to use OpenID connect authentication. Optional for HTTP mode.
            oidc_enabled:         true
            # Required for HTTP mode when oidc_enabled is true.
            oidc_provider_url:    ~
            # Required for HTTP mode when oidc_enabled is true.
            oidc_client_id:       ~
            # Required for HTTP mode when oidc_enabled is true.
            oidc_client_secret:   ~
            # Whether to send file content and metadata checksums for Blob to check
            send_checksums:       true
```

Minimal example configuration:

```yaml
dbp_relay_blob_connector_campusonline_dms:
    blob_library:
        bucket_identifier: "campusonline-dms-bucket"
        use_http_mode: false
    authorization:
        roles:
            ROLE_USER: 'user.get("SCOPE_CAMPUSONLINE_DMS")'
```

## Authorization

There only exists one "ROLE_USER" role, which if granted to the current user,
allows access to the CAMPUSonline External Object Store API.