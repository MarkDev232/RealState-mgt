import React from 'react';
import { clsx } from 'clsx';

interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  label?: string;
  error?: string;
  helperText?: string; // ✅ add this
}

export const Input: React.FC<InputProps> = ({
  label,
  error,
  helperText, // ✅ include in destructuring
  className,
  ...props
}) => {
  return (
    <div className="w-full">
      {label && (
        <label className="block text-sm font-medium text-gray-700 mb-1">
          {label}
        </label>
      )}

      <input
        className={clsx(
          'w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 transition',
          error
            ? 'border-red-500 focus:ring-red-500'
            : 'border-gray-300 focus:ring-blue-500',
          className
        )}
        {...props}
      />

      {error ? (
        <p className="mt-1 text-sm text-red-600">{error}</p>
      ) : helperText ? (
        <p className="mt-1 text-sm text-gray-500">{helperText}</p>
      ) : null}
    </div>
  );
};
