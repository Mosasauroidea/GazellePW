export const Link = ({ href, children, ...rest }) => {
  return (
    <a class="Link" href={href} {...rest}>
      {children}
    </a>
  )
}

export const ExternalLink = ({ children, ...rest }) => {
  return (
    <Link target="_blank" {...rest}>
      {children}
    </Link>
  )
}
