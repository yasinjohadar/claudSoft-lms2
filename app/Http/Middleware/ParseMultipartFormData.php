<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ParseMultipartFormData
{
    /**
     * Handle an incoming request.
     *
     * Fix for Laravel not parsing multipart/form-data with PUT/PATCH methods
     * This is a known PHP limitation where $_POST is only populated for POST requests
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only process PUT/PATCH requests with multipart/form-data
        if (in_array($request->method(), ['PUT', 'PATCH']) &&
            str_contains($request->header('Content-Type', ''), 'multipart/form-data')) {

            // Parse the raw input stream
            $this->parseMultipartFormData($request);
        }

        return $next($request);
    }

    /**
     * Parse multipart/form-data from php://input
     */
    private function parseMultipartFormData(Request $request): void
    {
        $contentType = $request->header('Content-Type');

        // Extract boundary from Content-Type header
        preg_match('/boundary=(.*)$/', $contentType, $matches);
        if (!isset($matches[1])) {
            return;
        }

        $boundary = $matches[1];

        // Try to get raw data from the request content (Symfony stores it)
        $rawData = $request->getContent();

        // If empty, php://input might have been consumed, skip parsing
        if (empty($rawData)) {
            return;
        }

        // Split the raw data by boundary
        $parts = array_slice(explode("--$boundary", $rawData), 1);
        $data = [];

        foreach ($parts as $part) {
            // Skip if it's the final boundary
            if (trim($part) === '--' || empty(trim($part))) {
                continue;
            }

            // Split headers and content
            $sections = explode("\r\n\r\n", $part, 2);
            if (count($sections) !== 2) {
                continue;
            }

            [$headers, $content] = $sections;

            // Parse the field name from Content-Disposition header
            if (preg_match('/name="([^"]*)"/', $headers, $nameMatch)) {
                $fieldName = $nameMatch[1];
                // Remove trailing \r\n from content
                $fieldValue = rtrim($content, "\r\n");

                // Handle array fields (e.g., course_ids[])
                if (str_ends_with($fieldName, '[]')) {
                    $fieldName = substr($fieldName, 0, -2);
                    if (!isset($data[$fieldName])) {
                        $data[$fieldName] = [];
                    }
                    $data[$fieldName][] = $fieldValue;
                } else {
                    $data[$fieldName] = $fieldValue;
                }
            }
        }

        // Merge the parsed data into the request
        if (!empty($data)) {
            $request->merge($data);
        }
    }
}
