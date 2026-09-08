# Operational Experiment Day 1

## Horizon Status
- Active
- Jobs Per Minute: 9
- Jobs Past Hour: 47
- Failed Jobs Past 7 Days: 147
- Total Processes: 4
- Busiest queue: notifications

## Normal Traffic
- Visited organizations, workspaces, projects, tasks
- Created/updated tasks and triggered notifications
- Accessed Filament admin

## Controlled Events
| Event | Observed? | Where | Notes |
|-------|-----------|-------|-------|
| Slow request | Yes | Pulse -> Slow Requests | `/control-43a701ff1f9dbead/reporting` ~8,060 ms |
| Slow query | Not yet | Pulse -> Slow Queries | pending |
| Successful job | Yes | Horizon -> Completed | notifications processed |
| Failed job | Yes | Horizon -> Failed | `LogActivityJob` MaxAttemptsExceeded |

## Pulse Observations
- Cache hit rate: 25.71% after Redis flush
- Top slow route: Reporting page
- `livewire-checksum-failures` accumulating misses from `172.18.0.1`
- Exceptions card disabled pending MySQL/Pulse fix verification

## Issues / Notes
- Exceptions card was disabled during baseline due to recurring JsonException.
- Redis was reset and Pulse data truncated before this traffic.
- `LogActivityJob` failed after retries; root cause not yet confirmed.

## Next Steps
- Inspect Laravel logs for `LogActivityJob` original exception
- Re-enable Exceptions card after confirming MySQL key_hash override
- Investigate Reporting page performance
- Investigate Livewire checksum failures
