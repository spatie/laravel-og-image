---
title: Clearing generated images
weight: 7
---

To delete all generated OG images from disk:

```bash
php artisan og-image:clear
```

This removes all files from the configured disk path. The images will be regenerated on the next request (as long as the URL is still in cache or the page is visited again).
