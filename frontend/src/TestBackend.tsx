import React, { useEffect, useState } from 'react';
import axios from 'axios';

const TestBackend: React.FC = () => {
  const [message, setMessage] = useState<string>('Checking connection...');
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    axios.get('http://127.0.0.1:8000/api/test')
      .then(res => setMessage(res.data.message))
      .catch(err => setError(err.message));
  }, []);

  return (
    <div className="p-6 max-w-md mx-auto mt-10 bg-white shadow rounded">
      <h1 className="text-xl font-bold mb-4">Backend Test</h1>
      {error ? (
        <p className="text-red-600">Error: {error}</p>
      ) : (
        <p className="text-green-600">{message}</p>
      )}
    </div>
  );
};

export default TestBackend;
