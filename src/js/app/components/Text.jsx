export const Important = ({ italic, children }) => {
  const Tag = italic ? 'i' : 'span'
  return <Tag className="u-colorWarning">{children}</Tag>
}

export const Center = ({ children }) => {
  return <div style={{ textAlign: 'center' }}>{children}</div>
}

export const Heading1 = (props) => <Heading as="h1" {...props} />
export const Heading2 = (props) => <Heading as="h2" {...props} />
export const Heading3 = (props) => <Heading as="h3" {...props} />
export const Heading4 = (props) => <Heading as="h4" {...props} />
export const Heading5 = (props) => <Heading as="h5" {...props} />
export const Heading6 = (props) => <Heading as="h6" {...props} />

const Heading = ({ as: Tag, id, children, ...rest }) => {
  if (id) {
    return (
      <Tag id={id} {...rest}>
        <a className="HtmlText-anchor" href={`#${id}`}>
          <svg
            className="HtmlText-anchorIcon"
            viewBox="0 0 16 16"
            version="1.1"
            width="16"
            height="16"
            aria-hidden="true"
          >
            <path
              fill="currentColor"
              fillRule="evenodd"
              d="M7.775 3.275a.75.75 0 001.06 1.06l1.25-1.25a2 2 0 112.83 2.83l-2.5 2.5a2 2 0 01-2.83 0 .75.75 0 00-1.06 1.06 3.5 3.5 0 004.95 0l2.5-2.5a3.5 3.5 0 00-4.95-4.95l-1.25 1.25zm-4.69 9.64a2 2 0 010-2.83l2.5-2.5a2 2 0 012.83 0 .75.75 0 001.06-1.06 3.5 3.5 0 00-4.95 0l-2.5 2.5a3.5 3.5 0 004.95 4.95l1.25-1.25a.75.75 0 00-1.06-1.06l-1.25 1.25a2 2 0 01-2.83 0z"
            ></path>
          </svg>
        </a>
        {children}
      </Tag>
    )
  }
  return <Tag {...rest}>{children}</Tag>
}

export const DonationProgress = ({ children }) => <div className="DonationPage-progress">{children}</div>
