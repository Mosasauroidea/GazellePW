const { danger, warn, fail } = require('danger')
const load = require('@commitlint/load').default
const lint = require('@commitlint/lint').default

async function main() {
  const options = await load({ extends: ['@commitlint/config-conventional'] })
  const report = await lint(danger.gitlab.mr.title, options.rules)
  for (const error of report.errors) {
    fail(error.message)
  }
  for (const warning of report.warnings) {
    warn(warning.message)
  }
}

main()
