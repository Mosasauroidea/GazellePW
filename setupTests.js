import fs from 'fs'
import path from 'path'

global.readFixture = function readFixture(dir, name) {
  return fs.readFileSync(path.join(dir, `fixtures/${name}.txt`)).toString()
}
