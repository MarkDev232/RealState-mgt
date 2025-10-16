import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import Layout from './components/layout/Layout';
import Home from './pages/Home';
import Properties from './pages/Properties';
import PropertyDetails from './pages/PropertyDetails';
import Login from './pages/Login';
import Register from './pages/Register';
import { PageLoader } from './components/common/Loader';
import { useAuth } from './hooks/useAuth';
import TestBackend from './TestBackend';

// Protected Route Component
const ProtectedRoute: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { isAuthenticated, loading } = useAuth();
  
  if (loading) {
    return <PageLoader />;
  }
  
  if (!isAuthenticated) {
    window.location.href = '/login';
    return null;
  }
  
  return <>{children}</>;
};

const App: React.FC = () => {
  return (
    <AuthProvider>
      <Router>
        <Layout>
          <Routes>
            <Route path="/" element={<Home />} />
            <Route path="/properties" element={<Properties />} />
            <Route path="/properties/:id" element={<PropertyDetails />} />
            <Route path="/test-backend" element={<TestBackend />} />
            <Route path="/login" element={<Login />} />
            <Route path="/register" element={<Register />} />
            {/* Protected Routes */}
            <Route 
              path="/dashboard/*" 
              element={
                <ProtectedRoute>
                  <div className="min-h-screen bg-gray-50 py-8">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                      <h1 className="text-3xl font-bold text-gray-900">Dashboard</h1>
                      <p className="mt-2 text-gray-600">Dashboard features coming soon...</p>
                    </div>
                  </div>
                </ProtectedRoute>
              } 
            />
            <Route 
              path="/profile" 
              element={
                <ProtectedRoute>
                  <div className="min-h-screen bg-gray-50 py-8">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                      <h1 className="text-3xl font-bold text-gray-900">Profile</h1>
                      <p className="mt-2 text-gray-600">Profile management coming soon...</p>
                    </div>
                  </div>
                </ProtectedRoute>
              } 
            />
            <Route 
              path="/favorites" 
              element={
                <ProtectedRoute>
                  <div className="min-h-screen bg-gray-50 py-8">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                      <h1 className="text-3xl font-bold text-gray-900">Favorites</h1>
                      <p className="mt-2 text-gray-600">Favorite properties coming soon...</p>
                    </div>
                  </div>
                </ProtectedRoute>
              } 
            />
          </Routes>
        </Layout>
      </Router>
    </AuthProvider>
  );
};

export default App;