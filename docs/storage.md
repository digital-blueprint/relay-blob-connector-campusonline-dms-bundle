# Blob Storage Layout

This bundle uses [dbp/relay-blob-bundle](https://packagist.org/packages/dbp/relay-blob-bundle)
as its sole persistence layer. There is no separate database — all document state is stored
as files in a configured Blob bucket.

## Documents and Versions

The bundle models two concepts:

- A **Document** is a logical container identified by a UUID (`docUid`). It holds no data of
  its own in Blob; it exists only as a grouping concept.
- A **Document Version** is the actual stored artifact. Every version of a document is stored
  as a separate Blob file.

```
Document (docUid)
├── Version 1  →  Blob file (identifier = versionUid)
├── Version 2  →  Blob file (identifier = versionUid)
└── Version N  →  Blob file (identifier = versionUid)
```

All Blob files belonging to the same document share the same `prefix` value (the `docUid`),
which is how they are grouped together.

## Blob File Fields

Each document version is stored as a single Blob file with the following fields:

| Blob field   | Value                                          | Notes                                                      |
|--------------|------------------------------------------------|------------------------------------------------------------|
| `identifier` | Auto-generated UUID                            | Becomes the `versionUid` returned by the API               |
| `prefix`     | `docUid`                                       | Groups all versions of the same document together          |
| `type`       | Configured `blob_type` (default: `document_version`) | See [`blob_type` in config](./config.md)             |
| `fileName`   | `name` request parameter                       | The filename submitted by CAMPUSonline                     |
| `file`       | Binary file content                            | The uploaded file data                                     |
| `metadata`   | JSON-encoded string                            | All document and version metadata (see below)              |

## Blob Metadata JSON

The `metadata` field of every Blob file is a JSON object with the following keys:

```json
{
  "version": "3",
  "doc_metadata": { },
  "doc_version_metadata": { },
  "doc_type": "RECORD_OF_STUDIES"
}
```

| Key                   | Required | Description                                                                 |
|-----------------------|----------|-----------------------------------------------------------------------------|
| `version`             | yes      | Version number as a string (e.g. `"1"`, `"2"`). Used to determine ordering |
| `doc_metadata`        | no       | Document-level metadata as submitted by CAMPUSonline on document creation   |
| `doc_version_metadata`| no       | Version-specific metadata as submitted by CAMPUSonline on version creation  |
| `doc_type`            | no       | The CAMPUSonline document type (e.g. `"RECORD_OF_STUDIES"`)                 |

The document-level metadata (`doc_metadata`) is copied into every Blob file of the document,
since there is no separate record for the document itself.

## Version Numbering

When a new version is created, the bundle determines its version number as follows:

1. The current latest version number is read and incremented by 1.
2. If the submitted metadata contains an `objectVersion.versionNumber` field, that value
   takes precedence over the auto-incremented number.

Version numbers are stored as strings and compared numerically.

## Resolving the Latest Version

There is no separate index or pointer to the latest version. When the latest version of a
document is needed (e.g. on `GET /documents/{uid}`), the bundle fetches all Blob files with
`prefix == docUid` and selects the one with the highest numeric `version` value in its
metadata.

## Deleting a Document

Deleting a document (`DELETE /documents/{uid}`) removes all Blob files that share the
document's `prefix`. There is no soft-delete or archival step.

## The `/files` API

The `/files` endpoints (`POST`, `GET`, `PUT`, `DELETE /co-dms-api/api/files`) are implemented
for CAMPUSonline API compatibility only. They do not store any data in Blob storage; they
accept requests and return a UUID without persisting anything.
