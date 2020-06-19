UPGRADE
=======

# Versions history

## 3.5.0 (2018050800)
## 3.6.0 (2018112800)
- Moodle 3.6 upgrade from 3.5

## 3.5.1 (2018050801)
- Events & xAPI

## 3.5.2 (2018050802)
- Issues and enhancements

## 3.5.3 (2018050803)
- Issues and enhancements

## 3.5.4 (2018050804)
- Issues


# Database impact

## UUID column
Added to 3.5.1


# Tests

## Basic usage
- Create simple struct with all activity types (incl. remedial) - OK
- Theme duration 60 min - OK
- Sequence duration 2 days - OK
- Activities duration 10 min - OK
- Add calendar - OK
- Generate schedule - OK
- Open all activities - OK
- As a learner, play all activities - OK
- Report Learners Progress - OK
    - Comment - OK
    - Global export - OK
    - Export themes - OK
    - Export users - OK
    - Global export (full) - OK
    - Export themes (full) - OK
    - Export users (full) - OK
- Report Learner - OK
    - Comment global - OK
    - Comment theme - OK
    - Export - OK
- Report Theme - OK
    - Comment - OK
    - Export - OK
    - Export Sequences - OK
- Report Sequence - OK
    - Comment - OK
    - Export - OK
- Report Content - OK
    - Force Completion - OK
    - Force Time spent - OK
- Report Assessment - OK
    - Force Score - OK
- Report Virtual/Onsite - OK
    - Add file - OK
    - Mark Time + Completion - OK

## Data privacy
- Check Admin > Users > Privacy and policies > Plugin privacy registry - OK
- Run CRON - OK
- Download and explore data - All users visible ?????

## Operations
- Duplicate - OK
- Backup / Restore - OK
- Reset - OK

## Events
- Attempt completed
- Attempt failed
- Attempt initialized
- Attempt launched
- Attempt passed
- Attempt terminated
- Course module instance list viewed
- Course module viewed
- Item completed
- Item completion forced
- Item result forced
- Item result updated
- Item viewed
- Page viewed

## xAPI

### Sync
- Attempt completed - OK
- Attempt failed
- Attempt initialized - OK
- Attempt launched - OK
- Attempt passed - OK
- Attempt terminated - OK
- Course module viewed - OK
- Item completed - OK
- Item completion forced
- Item result forced
- Item viewed - OK
- Page viewed - OK

### Async
Errors with Trax Logs which has not been upgraded yet !!!!!!!!!!!!!!!!!!!!!!

