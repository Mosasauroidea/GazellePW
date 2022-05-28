export default function isGpwHelperInjected() {
  return (
    typeof window.gpwHelper === 'object' &&
    window.gpwHelper.id === '6C406E43-E587-429A-B69C-BB5082BC7589'
  )
}
