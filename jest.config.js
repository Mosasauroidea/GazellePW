module.exports = {
  testMatch: ['**/__tests__/**/*.test.js'],
  setupFilesAfterEnv: ['<rootDir>/setupTests.js'],
  moduleDirectories: ['node_modules', 'src/js'],
  transform: {
    '\\.js$': 'babel-jest',
  },
  moduleNameMapper: { '^#(.*)$': '<rootDir>/src/js$1' },
}
