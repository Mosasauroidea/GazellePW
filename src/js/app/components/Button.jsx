export const Button = ({ href, children }) => {
  if (href) {
    return (
      <a class="Button" href={href}>
        {children}
      </a>
    )
  }
}
