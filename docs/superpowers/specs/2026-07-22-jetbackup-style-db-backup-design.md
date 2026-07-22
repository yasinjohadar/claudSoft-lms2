# JetBackup-Style Database Backup Engine — Design

**Date:** 2026-07-22  
**Status:** Approved  
**Scope (phase 1):** Database backups only; architecture extensible to files later

## Goals

- Dump large MySQL/MariaDB databases without loading the full SQL into PHP memory.
- Compress and upload to configured storage (including IDrive e2 via S3) using path/stream-based transfer (multipart where applicable).
- Real progress stages and reliable completion/failure (no permanent `running`).
- Pluggable `BackupSource` so a future `FilesBackupSource` plugs into the same engine.

## Non-goals (phase 1)

- Incremental / differential backups  
- Full JetBackup file-set restore UX  
- LVM / disk snapshots  

## Architecture

```
Admin UI / Schedule
       ↓
CreateBackupJob (long timeout, queued)
       ↓
BackupEngine
       ↓
DatabaseBackupSource (mysqldump) ──fallback──► PhpDatabaseBackupSource
       ↓
local artifact (.sql.gz)
       ↓
StorageManager::storeWithFailover → storeFromPath()
       ↓
S3 / IDrive / local / …
```

### Components

| Component | Role |
|-----------|------|
| `BackupSourceInterface` | `produce(Backup, onProgress): BackupArtifact` |
| `DatabaseBackupSource` | Native `mysqldump` / `mariadb-dump` |
| `PhpDatabaseBackupSource` | Legacy in-memory dump (fallback only) |
| `BackupArtifact` | local path, size, extension, mime |
| `BackupEngine` | Stages, logs, progress columns, cleanup |
| `BackupStorageInterface::storeFromPath` | Upload from disk without full-string load |
| `S3StorageDriver` | Multipart / stream upload |

## Stages

`preparing` → `dumping` → `compressing` → `uploading` → `verifying` → `completed` | `failed`

Persisted on `backups`: `progress`, `stage`, `bytes_processed`, `bytes_total`.

## Dump options

- Prefer `--single-transaction --quick --routines --triggers` for InnoDB  
- Credentials via defaults file or env (never log passwords)  
- Output under `storage/app/backups/tmp/{backup_id}/`

## Failure & stuck jobs

- Exceptions → `failed` + log + notification  
- Job `failOnTimeout`  
- Command or admin action: mark stale `running` as `failed`  
- Schedules dispatch `CreateBackupJob` (no inline heavy `createBackup` for DB)

## UI

- Progress bar from `progress` / `stage`  
- Log rows with `data-log-id` to prevent poll duplication  
- Optional mark-as-failed for stuck backups  

## Extensibility

Later: implement `FilesBackupSource` returning a tar/zip artifact; engine and storage path remain unchanged.
