import { useEffect, useState } from 'react'
import { Save, Plus, Trash2, Calendar, MapPin, Settings, AlertCircle } from 'lucide-react'
import AdminLayout from '@/components/AdminLayout'
import api from '@/lib/api'
import type { SystemSettings, Holiday, Location } from '@/types'

export default function AdminSettings() {
  const [settings, setSettings] = useState<SystemSettings | null>(null)
  const [holidays, setHolidays] = useState<Holiday[]>([])
  const [locations, setLocations] = useState<Location[]>([])
  const [loading, setLoading] = useState(true)
  const [saving, setSaving] = useState(false)
  const [activeTab, setActiveTab] = useState<'settings' | 'holidays' | 'locations'>('settings')

  const [newHoliday, setNewHoliday] = useState({ date: '', name: '', type: 'regular', is_recurring: false })
  const [newLocation, setNewLocation] = useState({ name: '', description: '', latitude: '', longitude: '', radius_meters: 100 })

  useEffect(() => {
    fetchData()
  }, [])

  const fetchData = async () => {
    setLoading(true)
    try {
      const [settingsRes, holidaysRes, locationsRes] = await Promise.all([
        api.get('/admin/settings').catch(() => ({ data: { data: {} } })),
        api.get('/admin/holidays').catch(() => ({ data: { data: [] } })),
        api.get('/admin/locations').catch(() => ({ data: { data: [] } })),
      ])
      setSettings(settingsRes.data.data)
      setHolidays(holidaysRes.data.data || [])
      setLocations(locationsRes.data.data || [])
    } catch (err) {
      console.error(err)
    } finally {
      setLoading(false)
    }
  }

  const updateSetting = async (key: string, value: string) => {
    setSaving(true)
    try {
      await api.put(`/admin/settings/${key}`, { value })
      alert('Setting updated')
    } catch (err) {
      console.error(err)
      alert('Failed to update setting')
    } finally {
      setSaving(false)
    }
  }

  const addHoliday = async () => {
    if (!newHoliday.date || !newHoliday.name) {
      alert('Please fill in date and name')
      return
    }
    try {
      await api.post('/admin/holidays', newHoliday)
      setNewHoliday({ date: '', name: '', type: 'regular', is_recurring: false })
      fetchData()
    } catch (err) {
      console.error(err)
      alert('Failed to add holiday')
    }
  }

  const deleteHoliday = async (id: string) => {
    if (!confirm('Delete this holiday?')) return
    try {
      await api.delete(`/admin/holidays/${id}`)
      setHolidays(holidays.filter(h => h.id !== id))
    } catch (err) {
      console.error(err)
      alert('Failed to delete holiday')
    }
  }

  const addLocation = async () => {
    if (!newLocation.name) {
      alert('Please enter a location name')
      return
    }
    try {
      await api.post('/admin/locations', {
        name: newLocation.name,
        description: newLocation.description,
        latitude: newLocation.latitude ? parseFloat(newLocation.latitude) : null,
        longitude: newLocation.longitude ? parseFloat(newLocation.longitude) : null,
        radius_meters: newLocation.radius_meters,
      })
      setNewLocation({ name: '', description: '', latitude: '', longitude: '', radius_meters: 100 })
      fetchData()
    } catch (err) {
      console.error(err)
      alert('Failed to add location')
    }
  }

  const tabs = [
    { id: 'settings' as const, label: 'System Settings', icon: Settings },
    { id: 'holidays' as const, label: 'Holidays', icon: Calendar },
    { id: 'locations' as const, label: 'Locations', icon: MapPin },
  ]

  if (loading) {
    return (
      <AdminLayout title="Settings">
        <div className="text-center py-12">Loading...</div>
      </AdminLayout>
    )
  }

  return (
    <AdminLayout title="Settings">
      <div className="mb-6">
        <div className="flex gap-2 border-b border-border">
          {tabs.map((tab) => {
            const Icon = tab.icon
            return (
              <button
                key={tab.id}
                onClick={() => setActiveTab(tab.id)}
                className={`flex items-center gap-2 px-4 py-3 text-sm transition-colors border-b-2 -mb-px ${
                  activeTab === tab.id
                    ? 'border-primary text-primary font-medium'
                    : 'border-transparent text-text-secondary hover:text-text-primary'
                }`}
              >
                <Icon size={18} />
                {tab.label}
              </button>
            )
          })}
        </div>
      </div>

      {activeTab === 'settings' && settings && (
        <div className="bg-surface rounded-lg shadow-md p-6">
          <h3 className="font-semibold mb-4">System Configuration</h3>
          <div className="space-y-4">
            <SettingRow
              label="QR Rotation (seconds)"
              value={settings.qr_rotation_seconds}
              onSave={(v) => updateSetting('qr_rotation_seconds', v)}
              saving={saving}
            />
            <SettingRow
              label="Max Scans Per Day"
              value={settings.max_scans_per_day}
              onSave={(v) => updateSetting('max_scans_per_day', v)}
              saving={saving}
            />
            <SettingRow
              label="Grace Period (minutes)"
              value={settings.grace_period_minutes}
              onSave={(v) => updateSetting('grace_period_minutes', v)}
              saving={saving}
            />
            <SettingRow
              label="Scan Debounce (seconds)"
              value={settings.scan_debounce_seconds}
              onSave={(v) => updateSetting('scan_debounce_seconds', v)}
              saving={saving}
            />
          </div>

          <h3 className="font-semibold mt-8 mb-4">Schedule Settings</h3>
          <div className="grid grid-cols-2 gap-4">
            <SettingRow
              label="AM Start"
              value={settings.schedule_am_start}
              onSave={(v) => updateSetting('schedule_am_start', v)}
              saving={saving}
            />
            <SettingRow
              label="AM End"
              value={settings.schedule_am_end}
              onSave={(v) => updateSetting('schedule_am_end', v)}
              saving={saving}
            />
            <SettingRow
              label="PM Start"
              value={settings.schedule_pm_start}
              onSave={(v) => updateSetting('schedule_pm_start', v)}
              saving={saving}
            />
            <SettingRow
              label="PM End"
              value={settings.schedule_pm_end}
              onSave={(v) => updateSetting('schedule_pm_end', v)}
              saving={saving}
            />
          </div>

          <h3 className="font-semibold mt-8 mb-4">Geolocation</h3>
          <div className="grid grid-cols-2 gap-4">
            <SettingRow
              label="Required"
              value={settings.geolocation_required}
              onSave={(v) => updateSetting('geolocation_required', v)}
              saving={saving}
            />
            <SettingRow
              label="Max Distance (meters)"
              value={settings.geolocation_max_distance}
              onSave={(v) => updateSetting('geolocation_max_distance', v)}
              saving={saving}
            />
          </div>
        </div>
      )}

      {activeTab === 'holidays' && (
        <div className="space-y-6">
          <div className="bg-surface rounded-lg shadow-md p-6">
            <h3 className="font-semibold mb-4">Add Holiday</h3>
            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
              <input
                type="date"
                value={newHoliday.date}
                onChange={(e) => setNewHoliday({ ...newHoliday, date: e.target.value })}
                className="px-3 py-2 border border-border rounded-md"
              />
              <input
                type="text"
                placeholder="Holiday Name"
                value={newHoliday.name}
                onChange={(e) => setNewHoliday({ ...newHoliday, name: e.target.value })}
                className="px-3 py-2 border border-border rounded-md"
              />
              <select
                value={newHoliday.type}
                onChange={(e) => setNewHoliday({ ...newHoliday, type: e.target.value })}
                className="px-3 py-2 border border-border rounded-md"
              >
                <option value="regular">Regular</option>
                <option value="special">Special</option>
                <option value="company">Company</option>
              </select>
              <button
                onClick={addHoliday}
                className="flex items-center justify-center gap-2 px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-dark"
              >
                <Plus size={18} />
                Add
              </button>
            </div>
            <label className="flex items-center gap-2 mt-3 text-sm">
              <input
                type="checkbox"
                checked={newHoliday.is_recurring}
                onChange={(e) => setNewHoliday({ ...newHoliday, is_recurring: e.target.checked })}
              />
              Recurring (every year)
            </label>
          </div>

          <div className="bg-surface rounded-lg shadow-md overflow-hidden">
            <table className="w-full text-sm">
              <thead className="bg-background">
                <tr>
                  <th className="px-4 py-3 text-left">Date</th>
                  <th className="px-4 py-3 text-left">Name</th>
                  <th className="px-4 py-3 text-left">Type</th>
                  <th className="px-4 py-3 text-center">Recurring</th>
                  <th className="px-4 py-3 text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                {holidays.length === 0 ? (
                  <tr>
                    <td colSpan={5} className="px-4 py-8 text-center text-text-secondary">
                      <AlertCircle className="mx-auto mb-2" size={24} />
                      No holidays configured
                    </td>
                  </tr>
                ) : (
                  holidays.map((holiday) => (
                    <tr key={holiday.id} className="border-t border-border">
                      <td className="px-4 py-3">{holiday.date}</td>
                      <td className="px-4 py-3 font-medium">{holiday.name}</td>
                      <td className="px-4 py-3">
                        <span className={`px-2 py-1 rounded text-xs ${
                          holiday.type === 'regular' ? 'bg-blue-100 text-blue-700' :
                          holiday.type === 'special' ? 'bg-purple-100 text-purple-700' :
                          'bg-green-100 text-green-700'
                        }`}>
                          {holiday.type}
                        </span>
                      </td>
                      <td className="px-4 py-3 text-center">
                        {holiday.is_recurring ? 'Yes' : 'No'}
                      </td>
                      <td className="px-4 py-3 text-center">
                        <button
                          onClick={() => deleteHoliday(holiday.id)}
                          className="text-danger hover:bg-red-50 p-1 rounded"
                        >
                          <Trash2 size={16} />
                        </button>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {activeTab === 'locations' && (
        <div className="space-y-6">
          <div className="bg-surface rounded-lg shadow-md p-6">
            <h3 className="font-semibold mb-4">Add Location</h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <input
                type="text"
                placeholder="Location Name"
                value={newLocation.name}
                onChange={(e) => setNewLocation({ ...newLocation, name: e.target.value })}
                className="px-3 py-2 border border-border rounded-md"
              />
              <input
                type="text"
                placeholder="Description"
                value={newLocation.description}
                onChange={(e) => setNewLocation({ ...newLocation, description: e.target.value })}
                className="px-3 py-2 border border-border rounded-md"
              />
              <input
                type="number"
                step="any"
                placeholder="Latitude (optional)"
                value={newLocation.latitude}
                onChange={(e) => setNewLocation({ ...newLocation, latitude: e.target.value })}
                className="px-3 py-2 border border-border rounded-md"
              />
              <input
                type="number"
                step="any"
                placeholder="Longitude (optional)"
                value={newLocation.longitude}
                onChange={(e) => setNewLocation({ ...newLocation, longitude: e.target.value })}
                className="px-3 py-2 border border-border rounded-md"
              />
              <input
                type="number"
                placeholder="Radius (meters)"
                value={newLocation.radius_meters}
                onChange={(e) => setNewLocation({ ...newLocation, radius_meters: parseInt(e.target.value) || 100 })}
                className="px-3 py-2 border border-border rounded-md"
              />
              <button
                onClick={addLocation}
                className="flex items-center justify-center gap-2 px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-dark"
              >
                <Plus size={18} />
                Add Location
              </button>
            </div>
          </div>

          <div className="bg-surface rounded-lg shadow-md overflow-hidden">
            <table className="w-full text-sm">
              <thead className="bg-background">
                <tr>
                  <th className="px-4 py-3 text-left">Name</th>
                  <th className="px-4 py-3 text-left">Description</th>
                  <th className="px-4 py-3 text-center">Status</th>
                </tr>
              </thead>
              <tbody>
                {locations.length === 0 ? (
                  <tr>
                    <td colSpan={3} className="px-4 py-8 text-center text-text-secondary">
                      <AlertCircle className="mx-auto mb-2" size={24} />
                      No locations configured
                    </td>
                  </tr>
                ) : (
                  locations.map((location) => (
                    <tr key={location.id} className="border-t border-border">
                      <td className="px-4 py-3 font-medium">{location.name}</td>
                      <td className="px-4 py-3 text-text-secondary">{location.description || '-'}</td>
                      <td className="px-4 py-3 text-center">
                        <span className={`px-2 py-1 rounded text-xs ${
                          location.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'
                        }`}>
                          {location.is_active ? 'Active' : 'Inactive'}
                        </span>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        </div>
      )}
    </AdminLayout>
  )
}

function SettingRow({ label, value, onSave, saving }: { label: string; value: string; onSave: (v: string) => void; saving: boolean }) {
  const [editValue, setEditValue] = useState(value)
  const [editing, setEditing] = useState(false)

  useEffect(() => {
    setEditValue(value)
  }, [value])

  const handleSave = () => {
    if (editValue !== value) {
      onSave(editValue)
    }
    setEditing(false)
  }

  return (
    <div className="flex items-center justify-between py-2">
      <span className="text-sm">{label}</span>
      {editing ? (
        <div className="flex items-center gap-2">
          <input
            type="text"
            value={editValue}
            onChange={(e) => setEditValue(e.target.value)}
            className="w-24 px-2 py-1 border border-border rounded text-sm"
          />
          <button
            onClick={handleSave}
            disabled={saving}
            className="p-1 text-primary hover:bg-primary/10 rounded"
          >
            <Save size={16} />
          </button>
          <button
            onClick={() => { setEditValue(value); setEditing(false) }}
            className="p-1 text-text-secondary hover:bg-gray-100 rounded text-xs"
          >
            Cancel
          </button>
        </div>
      ) : (
        <button
          onClick={() => setEditing(true)}
          className="font-mono text-sm hover:bg-background px-2 py-1 rounded"
        >
          {value}
        </button>
      )}
    </div>
  )
}
