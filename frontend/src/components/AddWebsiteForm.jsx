import { useState } from 'react'
import apiClient from '../api/client'

const ENVIRONMENTS = ['production', 'staging']
const SCHEDULES = ['hourly', 'every_6_hours', 'daily', 'weekly']

function AddWebsiteForm({ onCreated }) {
  const [name, setName] = useState('')
  const [baseUrl, setBaseUrl] = useState('')
  const [environment, setEnvironment] = useState(ENVIRONMENTS[0])
  const [schedule, setSchedule] = useState(SCHEDULES[0])
  const [error, setError] = useState(null)
  const [submitting, setSubmitting] = useState(false)

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError(null)
    setSubmitting(true)
    try {
      const res = await apiClient.post('/websites', {
        name,
        base_url: baseUrl,
        environment,
        schedule,
      })
      setName('')
      setBaseUrl('')
      setEnvironment(ENVIRONMENTS[0])
      setSchedule(SCHEDULES[0])
      onCreated(res.data)
    } catch (err) {
      setError(err.response?.data?.message ?? 'Could not register website.')
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <form onSubmit={handleSubmit} className="rounded-lg border border-gray-200 bg-white p-4">
      <h3 className="mb-3 text-sm font-medium text-gray-900">Register a Website</h3>
      {error && <p className="mb-3 text-sm text-red-600">{error}</p>}
      <div className="grid grid-cols-1 gap-3 sm:grid-cols-4">
        <div className="sm:col-span-2">
          <label className="block text-xs font-medium text-gray-700">Name</label>
          <input
            required
            value={name}
            onChange={(e) => setName(e.target.value)}
            className="mt-1 w-full rounded-md border border-gray-300 px-3 py-1.5 text-sm"
          />
        </div>
        <div className="sm:col-span-2">
          <label className="block text-xs font-medium text-gray-700">Base URL</label>
          <input
            type="url"
            required
            placeholder="https://example.com"
            value={baseUrl}
            onChange={(e) => setBaseUrl(e.target.value)}
            className="mt-1 w-full rounded-md border border-gray-300 px-3 py-1.5 text-sm"
          />
        </div>
        <div>
          <label className="block text-xs font-medium text-gray-700">Environment</label>
          <select
            value={environment}
            onChange={(e) => setEnvironment(e.target.value)}
            className="mt-1 w-full rounded-md border border-gray-300 px-3 py-1.5 text-sm capitalize"
          >
            {ENVIRONMENTS.map((value) => (
              <option key={value} value={value}>{value}</option>
            ))}
          </select>
        </div>
        <div>
          <label className="block text-xs font-medium text-gray-700">Schedule</label>
          <select
            value={schedule}
            onChange={(e) => setSchedule(e.target.value)}
            className="mt-1 w-full rounded-md border border-gray-300 px-3 py-1.5 text-sm capitalize"
          >
            {SCHEDULES.map((value) => (
              <option key={value} value={value}>{value.replaceAll('_', ' ')}</option>
            ))}
          </select>
        </div>
      </div>
      <button
        type="submit"
        disabled={submitting}
        className="mt-3 rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50"
      >
        {submitting ? 'Registering…' : 'Register Website'}
      </button>
    </form>
  )
}

export default AddWebsiteForm
