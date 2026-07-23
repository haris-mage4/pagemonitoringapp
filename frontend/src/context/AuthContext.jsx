import { createContext, useContext, useEffect, useState } from 'react'
import apiClient from '../api/client'

const AuthContext = createContext(null)

export function AuthProvider({ children }) {
  const [token, setToken] = useState(() => localStorage.getItem('auth_token'))
  const [user, setUser] = useState(null)
  const [loading, setLoading] = useState(Boolean(token))

  useEffect(() => {
    if (!token) {
      setLoading(false)
      return
    }

    apiClient
      .get('/auth/me')
      .then((res) => setUser(res.data))
      .catch(() => {
        setToken(null)
        localStorage.removeItem('auth_token')
      })
      .finally(() => setLoading(false))
  }, [token])

  const login = async (email, password) => {
    const res = await apiClient.post('/auth/login', { email, password })
    localStorage.setItem('auth_token', res.data.token)
    setToken(res.data.token)
    setUser(res.data.user)
  }

  const register = async (name, email, password, passwordConfirmation) => {
    const res = await apiClient.post('/auth/register', {
      name,
      email,
      password,
      password_confirmation: passwordConfirmation,
    })
    localStorage.setItem('auth_token', res.data.token)
    setToken(res.data.token)
    setUser(res.data.user)
  }

  const logout = async () => {
    try {
      await apiClient.post('/auth/logout')
    } finally {
      localStorage.removeItem('auth_token')
      setToken(null)
      setUser(null)
    }
  }

  return (
    <AuthContext.Provider value={{ token, user, loading, login, register, logout }}>
      {children}
    </AuthContext.Provider>
  )
}

export function useAuth() {
  return useContext(AuthContext)
}
