import { useEffect, useRef, useState, useCallback } from 'react'

interface ClockEvent {
  type: 'clock_event'
  data: {
    student_name: string
    log_type: string
    log_category: string
    timestamp: string
  }
}

export function useWebSocket(url: string = 'ws://localhost:8000/ws/attendance') {
  const [isConnected, setIsConnected] = useState(false)
  const [lastEvent, setLastEvent] = useState<ClockEvent | null>(null)
  const wsRef = useRef<WebSocket | null>(null)
  const reconnectTimeoutRef = useRef<number | null>(null)

  const connect = useCallback(() => {
    if (wsRef.current?.readyState === WebSocket.OPEN) return

    const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:'
    const host = window.location.host
    const wsUrl = `${protocol}//${host}/ws/attendance`
    
    try {
      const ws = new WebSocket(wsUrl)
      
      ws.onopen = () => {
        setIsConnected(true)
        console.log('WebSocket connected')
      }
      
      ws.onmessage = (event) => {
        try {
          const data = JSON.parse(event.data)
          if (data.type === 'clock_event') {
            setLastEvent(data)
          }
        } catch (e) {
          console.error('WebSocket message parse error:', e)
        }
      }
      
      ws.onclose = () => {
        setIsConnected(false)
        console.log('WebSocket disconnected')
        reconnectTimeoutRef.current = window.setTimeout(() => {
          connect()
        }, 5000)
      }
      
      ws.onerror = (error) => {
        console.error('WebSocket error:', error)
      }
      
      wsRef.current = ws
    } catch (error) {
      console.error('WebSocket connection failed:', error)
    }
  }, [])

  const disconnect = useCallback(() => {
    if (reconnectTimeoutRef.current) {
      clearTimeout(reconnectTimeoutRef.current)
    }
    wsRef.current?.close()
    wsRef.current = null
    setIsConnected(false)
  }, [])

  useEffect(() => {
    connect()
    return () => disconnect()
  }, [connect, disconnect])

  return { isConnected, lastEvent, connect, disconnect }
}
