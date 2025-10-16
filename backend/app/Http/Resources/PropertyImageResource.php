<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'property_id' => $this->property_id,
            'image_path' => $this->image_path,
            'image_url' => $this->image_url,
            'is_primary' => $this->is_primary,
            'order' => $this->order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Additional image information
            'file_info' => [
                'filename' => basename($this->image_path),
                'extension' => pathinfo($this->image_path, PATHINFO_EXTENSION),
                'size' => $this->getFileSize(), // You would need to implement this method
            ],

            // Thumbnail URLs (if you generate thumbnails)
            'thumbnails' => [
                'small' => $this->getThumbnailUrl('small'),
                'medium' => $this->getThumbnailUrl('medium'),
                'large' => $this->getThumbnailUrl('large'),
            ],

            // Dimensions (if you store them)
            'dimensions' => [
                'width' => $this->width,
                'height' => $this->height,
            ],

            // Action permissions
            'permissions' => [
                'can_set_primary' => !$this->is_primary,
                'can_delete' => true, // Add your logic here
            ],

            // URLs
            'url' => $this->image_url,
            'api_url' => route('api.property-images.show', $this->id),
        ];
    }

    /**
     * Get file size in human readable format
     */
    private function getFileSize(): string
    {
        // This is a placeholder - you would need to implement actual file size retrieval
        // Example implementation if you store file sizes:
        // return $this->file_size ? $this->formatBytes($this->file_size) : 'Unknown';
        
        return 'Unknown';
    }

    /**
     * Get thumbnail URL for different sizes
     */
    private function getThumbnailUrl(string $size): string
    {
        // This is a placeholder - implement your thumbnail generation logic
        // Example: return route('image.thumbnail', ['size' => $size, 'image' => $this->image_path]);
        
        return $this->image_url; // Fallback to original image
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'version' => '1.0.0',
                'api_version' => 'v1',
                'image_processing' => [
                    'thumbnails_available' => false, // Set to true if you generate thumbnails
                    'max_file_size' => '5MB',
                    'allowed_formats' => ['jpeg', 'png', 'jpg', 'gif'],
                ],
            ],
        ];
    }
}