import { cva } from 'class-variance-authority';

export { default as Badge } from './Badge.vue';

export const badgeVariants = cva(
  'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none',
  {
    variants: {
      variant: {
        default: 'border-transparent bg-primary text-primary-foreground',
        secondary: 'border-transparent bg-secondary text-secondary-foreground',
        destructive: 'border-transparent bg-destructive text-destructive-foreground',
        outline: 'text-foreground',
        success: 'border-transparent bg-success text-success-foreground',
        'success-soft': 'border-transparent bg-success-soft text-success-strong',
        warning: 'border-transparent bg-warning text-warning-foreground',
        'warning-soft': 'border-transparent bg-warning-soft text-warning-strong',
        info: 'border-transparent bg-info text-info-foreground',
        'info-soft': 'border-transparent bg-info-soft text-info-strong',
        soft: 'border-transparent bg-primary-soft text-primary-strong',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
);
