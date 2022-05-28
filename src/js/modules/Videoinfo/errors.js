export class VideoinfoError extends Error {
  constructor(message) {
    super(message)
    Object.defineProperty(this, 'name', {
      value: this.constructor.name,
      enumerable: false,
    })
  }
}

export class VideinfoTableSpaceError extends VideoinfoError {}
