# Legacy Reference

Use `MilkMan_web/` and the `milkyway` schema to understand required behavior and table coverage, not to import test data.

Primary references:

- Admin dispatcher: `MilkMan_web/controller/mrequire.php`
- Database helper: `MilkMan_web/controller/medico.php`
- Customer API: `MilkMan_web/user_api/`
- Store API: `MilkMan_web/store_api/`
- Rider API: `MilkMan_web/rider_api/`
- Graph: `MilkMan_web/graphify-out/graph.json`
- Coverage tracker: `docs/schema-coverage.md`

When tracing behavior, identify request fields, response fields, touched tables, side effects, uploads, and notification behavior.

Every legacy application table must be mapped, merged into a documented Laravel model, or explicitly marked obsolete with a reason.
