import { useEffect, useState } from 'react'
import api from '@/lib/api'

interface QRData {
  qr_data: string
  seconds_remaining: number
  location: { name: string }
}

export default function GuardDisplay() {
  const [qrData, setQrData] = useState<QRData | null>(null)
  const [time, setTime] = useState(new Date())
  const [scanCount, setScanCount] = useState(0)

  useEffect(() => {
    const timer = setInterval(() => setTime(new Date()), 1000)
    return () => clearInterval(timer)
  }, [])

  useEffect(() => {
    fetchQR()
    const interval = setInterval(fetchQR, 30000)
    return () => clearInterval(interval)
  }, [])

  const fetchQR = async () => {
    try {
      const { data } = await api.get('/qr/current')
      setQrData(data.data)
    } catch (err) {
      console.error(err)
    }
  }

  return (
    <div className="min-h-screen bg-surface flex flex-col items-center justify-center p-4">
      <div className="text-center mb-4">
        <h1 className="text-3xl font-bold text-primary">OJT TIME LOG</h1>
        <p className="text-text-secondary">Scan to Clock In/Out</p>
      </div>
      
      <div className="bg-white p-6 rounded-lg shadow-lg">
        {qrData ? (
          <img src={`data:image/png;base64,${qrData.qr_data}`} alt="QR Code" className="w-64 h-64" />
        ) : (
          <div className="w-64 h-64 flex items-center justify-center bg-background">Loading...</div>
        )}
      </div>
      
      {qrData && (
        <div className="mt-4 text-center">
          <p className="text-lg">Refreshing in: <span className="font-bold text-primary">{qrData.seconds_remaining}s</span></p>
          <div className="w-64 h-2 bg-background rounded-full mt-2">
            <div 
              className="h-full bg-primary rounded-full transition-all"
              style={{ width: `${(qrData.seconds_remaining / 30) * 100}%` }}
            />
          </div>
        </div>
      )}
      
      <div className="mt-6 text-center text-text-secondary">
        <p>{qrData?.location?.name || 'Main Gate'}</p>
        <p className="text-2xl font-bold text-text-primary mt-2">
          {time.toLocaleTimeString()}
        </p>
        <p>{time.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</p>
      </div>
      
      <div className="mt-4 px-4 py-2 bg-background rounded-full">
        <span className="text-text-secondary">Today's scans: </span>
        <span className="font-bold text-primary">{scanCount}</span>
      </div>
    </div>
  )
}
