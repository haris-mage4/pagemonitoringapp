import { useState } from 'react'
import apiClient from '../api/client'

const PAGE_TYPES = ['homepage', 'cms', 'category', 'product', 'custom']

function AddPageForm({ websiteId, onCreated }) {
  const [url, setUrl] = useState('')
  const [pageType, setPageType] = useState(PAGE_TYPES[0])
  const [error, setError] = useState(null)
  const [submitting, setSubmitting] = useState(false)

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError(null)
    setSubmitting(true)
    try {
      const res = await apiClient.post(`/websites/${websiteId}/pages`, {
        url,
        page_type: pageType,
      })
      setUrl('')
      setPageType(PAGE_TYPES[0])
      onCreated(res.data)
    } catch (err) {
      setError(err.response?.data?.message ?? 'Could not add page.')
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <form onSubmit={handleSubmit} className="mb-4 border-b border-gray-100 pb-4">
      {error && <p className="mb-2 text-sm text-red-600">{error}</p>}
      <div className="flex flex-wrap items-end gap-3">
        <div className="flex-1 min-w-[200px]">
          <label className="block text-xs font-medium text-gray-700">Page URL</label>
          <input
            type="url"
            required
            placeholder="https://example.com/page"
            value={url}
            onChange={(e) => setUrl(e.target.value)}
            className="mt-1 w-full rounded-md border border-gray-300 px-3 py-1.5 text-sm"
          />
        </div>
        <div>
          <label className="block text-xs font-medium text-gray-700">Type</label>
          <select
            value={pageType}
            onChange={(e) => setPageType(e.target.value)}
            className="mt-1 rounded-md border border-gray-300 px-3 py-1.5 text-sm capitalize"
          >
            {PAGE_TYPES.map((value) => (
              <option key={value} value={value}>{value}</option>
            ))}
          </select>
        </div>
        <button
          type="submit"
          disabled={submitting}
          className="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50"
        >
          {submitting ? 'Adding…' : 'Add Page'}
        </button>
      </div>
    </form>
  )
}

export default AddPageForm
