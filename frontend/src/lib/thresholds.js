// Lighthouse's standard good / needs-improvement / poor bands per metric.
const THRESHOLDS = {
  performance: { good: 90, poor: 50, higherIsBetter: true, max: 100 },
  accessibility: { good: 90, poor: 50, higherIsBetter: true, max: 100 },
  seo: { good: 90, poor: 50, higherIsBetter: true, max: 100 },
  best_practices: { good: 90, poor: 50, higherIsBetter: true, max: 100 },
  lcp: { good: 2500, poor: 4000, higherIsBetter: false },
  cls: { good: 0.1, poor: 0.25, higherIsBetter: false },
  tbt: { good: 200, poor: 600, higherIsBetter: false },
}

export const STATUS_COLORS = {
  good: '#16a34a',
  needs_improvement: '#d97706',
  poor: '#dc2626',
  unknown: '#4f46e5',
}

export const STATUS_LABELS = {
  good: 'Good',
  needs_improvement: 'Needs improvement',
  poor: 'Poor',
}

export function getThresholds(metric) {
  return THRESHOLDS[metric] ?? null
}

export function getStatus(metric, value) {
  const t = THRESHOLDS[metric]
  if (!t || value == null) return null

  if (t.higherIsBetter) {
    if (value >= t.good) return 'good'
    if (value >= t.poor) return 'needs_improvement'
    return 'poor'
  }

  if (value <= t.good) return 'good'
  if (value <= t.poor) return 'needs_improvement'
  return 'poor'
}

export function statusColor(metric, value) {
  const status = getStatus(metric, value)
  return status ? STATUS_COLORS[status] : STATUS_COLORS.unknown
}
