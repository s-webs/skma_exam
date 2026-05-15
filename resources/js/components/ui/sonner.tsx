import { Toaster as Sonner } from "sonner"

type ToasterProps = React.ComponentProps<typeof Sonner>

const Toaster = ({ ...props }: ToasterProps) => {
  return (
    <Sonner
      theme="light"
      className="toaster group"
      style={{
        '--normal-bg': '#ffffff',
        '--normal-border': '#e5e7eb',
        '--normal-text': '#111827',
        '--error-bg': '#fef2f2',
        '--error-border': '#fca5a5',
        '--error-text': '#7f1d1d',
      } as React.CSSProperties}
      toastOptions={{
        style: {
          background: 'var(--normal-bg)',
          border: '1px solid var(--normal-border)',
          color: 'var(--normal-text)',
        },
        className: 'toast',
      }}
      {...props}
    />
  )
}

export { Toaster }
