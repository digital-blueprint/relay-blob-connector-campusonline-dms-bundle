# Provided API

This implements the "CAMPUSonline External Object Store API".

The OpenAPI specification and other details are not publicly available, but can
be requested by contacting CAMPUSonline at <campusonline@tugraz.at>

## Managing Documents

* POST:   `/co-dms-api/api/documents`
* GET:    `/co-dms-api/api/documents/{uid}`
* DELETE: `/co-dms-api/api/documents/{uid}`

## Managing Document Versions

* POST:   `/co-dms-api/api/documents/{uid}/version`
* DELETE: `/co-dms-api/api/documents/{docUid}/versions/{versionUid}`
* GET:    `/co-dms-api/api/documents/{docUid}/versions/{versionUid}/content`
* GET:    `/co-dms-api/api/documents/{docUid}/versions/{versionUid}/metadata`

## Managing Files

* POST:   `/co-dms-api/api/files`
* GET:    `/co-dms-api/api/files/{uid}`
* PUT:    `/co-dms-api/api/files/{uid}`
* DELETE: `/co-dms-api/api/files/{uid}`

## Health Check

* GET:    `/co-dms-api/api/health`
