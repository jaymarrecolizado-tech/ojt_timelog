import { useEffect, useRef, useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { Html5Qrcode } from 'html5-qrcode'
import { ArrowLeft, Check, X } from 'lucide-react'
import api from '@/lib/api'
import type { QRValidationResponse } from '@/types'

export default function ScanQR() {
  const [scanning, setScanning] = useState(false)
  const [result, setResult] = useState<QRValidationResponse | null>(null)
  const [error, setError] = useState('')
  const scannerRef = useRef<Html5Qrcode | null>(null)
  const navigate = useNavigate()

  useEffect(() => {
    return () => {
      if (scannerRef.current) {
        scannerRef.current.stop().catch(() => {})
      }
    }
  }, [])

  const startScanning = async () => {
    setError('')
    setScanning(true)
    
    try {
      scannerRef.current = new Html5Qrcode('qr-reader')
      
      await scannerRef.current.start(
        { facingMode: 'environment' },
        { fps: 10, qrbox: { width: 250, height: 250 } },
        async (decodedText) => {
          await handleScan(decodedText)
        },
        () => {}
      )
    } catch (err) {
      setError('Could not access camera. Please allow camera permissions.')
      setScanning(false)
    }
  }

  const handleScan = async (token: string) => {
    if (scannerRef.current) {
      await scannerRef.current.stop()
    }
    setScanning(false)
    
    try {
      const { data } = await api.post('/qr/validate', { token })
      setResult(data.data)
    } catch (err: any) {
      setError(err.response?.data?.detail || 'QR validation failed')
    }
  }

  const resetScan = () => {
    setResult(null)
    setError('')
    startScanning()
  }

  return (
    <div className="min-h-screen bg-background">
      <header className="bg-surface shadow-sm px-4 py-3 flex items-center gap-3">
        <Link to="/student/dashboard" className="text-text-secondary hover:text-text-primary">
          <ArrowLeft size={24} />
        </Link>
        <h1 className="font-semibold">Scan QR Code</h1>
      </header>
      
      <main className="p-4 max-w-md mx-auto">
        {!scanning && !result && !error && (
          <div className="text-center py-8">
            <p className="text-text-secondary mb-6">Point your camera at the QR code on the guard's device</p>
            <button onClick={startScanning} className="bg-primary text-white px-6 py-3 rounded-lg font-semibold">
              Start Scanning
            </button>
          </div>
        )}
        
        {scanning && (
          <div>
            <div id="qr-reader" className="w-full"></div>
            <p className="text-center text-sm text-text-secondary mt-4">Scanning...</p>
          </div>
        )}
        
        {error && (
          <div className="text-center py-8">
            <div className="w-20 h-20 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center">
              <X className="text-danger" size={40} />
            </div>
            <p className="text-danger font-semibold mb-2">Scan Failed</p>
            <p className="text-text-secondary text-sm mb-6">{error}</p>
            <button onClick={resetScan} className="bg-primary text-white px-6 py-3 rounded-lg font-semibold">
              Try Again
            </button>
          </div>
        )}
        
        {result && (
          <div className="text-center py-8">
            <div className="w-20 h-20 mx-auto mb-4 bg-green-100 rounded-full flex items-center justify-center">
              <Check className="text-success" size={40} />
            </div>
            <p className="text-success font-semibold text-lg mb-1">SUCCESS!</p>
            <p className="text-2xl font-bold mb-1">
              TIME {result.log_type} - {result.log_category}
            </p>
            <p className="text-3xl font-bold mb-4">{result.formatted_time}</p>
            <p className="text-text-secondary mb-6">{result.message}</p>
            <Link to="/student/dashboard" className="block">
              <button className="w-full bg-primary text-white py-3 rounded-lg font-semibold">
                Back to Dashboard
              </button>
            </Link>
          </div>
        )}
      </main>
    </div>
  )
}
