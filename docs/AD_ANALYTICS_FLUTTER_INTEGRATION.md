# Flutter App - Ad Analytics Integration Guide

## Overview

This guide shows how to integrate ad impression and click tracking from the SeferEt Flutter mobile app to the Laravel backend analytics system.

---

## Prerequisites

- Flutter app with HTTP client configured
- API base URL configured
- Session management implemented
- Device type detection available

---

## 1. Create Analytics Service

Create a new service file for ad tracking:

```dart
// lib/services/ad_analytics_service.dart

import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:flutter/foundation.dart';

class AdAnalyticsService {
  final String baseUrl;
  final http.Client client;
  
  // Cache to prevent duplicate tracking within 5 minutes
  final Map<String, DateTime> _impressionCache = {};
  final Map<String, DateTime> _clickCache = {};
  final Duration _cacheDuration = const Duration(minutes: 5);

  AdAnalyticsService({
    required this.baseUrl,
    http.Client? client,
  }) : client = client ?? http.Client();

  /// Track ad impression
  Future<bool> trackImpression({
    required int adId,
    String? deviceType,
    String? placement,
  }) async {
    try {
      // Check cache to prevent duplicates
      final cacheKey = 'impression_$adId';
      if (_isRecentlyTracked(_impressionCache, cacheKey)) {
        debugPrint('Impression already tracked recently for ad $adId');
        return true;
      }

      final response = await client.post(
        Uri.parse('$baseUrl/api/v1/ads/$adId/track/impression'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'device_type': deviceType ?? _getDeviceType(),
          'placement': placement,
        }),
      ).timeout(const Duration(seconds: 5));

      if (response.statusCode == 200) {
        _impressionCache[cacheKey] = DateTime.now();
        debugPrint('Impression tracked for ad $adId');
        return true;
      } else {
        debugPrint('Failed to track impression: ${response.statusCode}');
        return false;
      }
    } catch (e) {
      debugPrint('Error tracking impression: $e');
      return false;
    }
  }

  /// Track ad click
  Future<bool> trackClick({
    required int adId,
    String? deviceType,
    String? placement,
  }) async {
    try {
      // Check cache to prevent duplicates
      final cacheKey = 'click_$adId';
      if (_isRecentlyTracked(_clickCache, cacheKey)) {
        debugPrint('Click already tracked recently for ad $adId');
        return true;
      }

      final response = await client.post(
        Uri.parse('$baseUrl/api/v1/ads/$adId/track/click'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'device_type': deviceType ?? _getDeviceType(),
          'placement': placement,
        }),
      ).timeout(const Duration(seconds: 5));

      if (response.statusCode == 200) {
        _clickCache[cacheKey] = DateTime.now();
        debugPrint('Click tracked for ad $adId');
        return true;
      } else {
        debugPrint('Failed to track click: ${response.statusCode}');
        return false;
      }
    } catch (e) {
      debugPrint('Error tracking click: $e');
      return false;
    }
  }

  /// Batch track multiple impressions (more efficient)
  Future<bool> trackImpressionsBatch(List<Map<String, dynamic>> impressions) async {
    try {
      final response = await client.post(
        Uri.parse('$baseUrl/api/v1/ads/track/impressions/batch'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: jsonEncode({
          'impressions': impressions.map((imp) => {
            'ad_id': imp['ad_id'],
            'device_type': imp['device_type'] ?? _getDeviceType(),
            'placement': imp['placement'],
          }).toList(),
        }),
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        debugPrint('Batch impressions tracked: ${impressions.length}');
        return true;
      } else {
        debugPrint('Failed to track batch impressions: ${response.statusCode}');
        return false;
      }
    } catch (e) {
      debugPrint('Error tracking batch impressions: $e');
      return false;
    }
  }

  /// Check if item was recently tracked
  bool _isRecentlyTracked(Map<String, DateTime> cache, String key) {
    if (!cache.containsKey(key)) return false;
    
    final lastTracked = cache[key]!;
    final now = DateTime.now();
    
    if (now.difference(lastTracked) > _cacheDuration) {
      cache.remove(key);
      return false;
    }
    
    return true;
  }

  /// Get device type
  String _getDeviceType() {
    if (defaultTargetPlatform == TargetPlatform.iOS ||
        defaultTargetPlatform == TargetPlatform.android) {
      return 'mobile';
    } else if (defaultTargetPlatform == TargetPlatform.linux ||
        defaultTargetPlatform == TargetPlatform.macOS ||
        defaultTargetPlatform == TargetPlatform.windows) {
      return 'desktop';
    }
    return 'unknown';
  }

  /// Clear tracking cache (useful for testing or logout)
  void clearCache() {
    _impressionCache.clear();
    _clickCache.clear();
  }

  /// Dispose client
  void dispose() {
    client.close();
  }
}
```

---

## 2. Create Ad Widget with Tracking

Create a reusable ad widget that automatically tracks impressions and clicks:

```dart
// lib/widgets/tracked_ad_banner.dart

import 'package:flutter/material.dart';
import 'package:visibility_detector/visibility_detector.dart';
import '../services/ad_analytics_service.dart';
import '../models/ad_model.dart';

class TrackedAdBanner extends StatefulWidget {
  final AdModel ad;
  final String placement;
  final AdAnalyticsService analyticsService;
  final VoidCallback? onTap;
  final double visibilityThreshold;

  const TrackedAdBanner({
    Key? key,
    required this.ad,
    required this.placement,
    required this.analyticsService,
    this.onTap,
    this.visibilityThreshold = 0.5,
  }) : super(key: key);

  @override
  State<TrackedAdBanner> createState() => _TrackedAdBannerState();
}

class _TrackedAdBannerState extends State<TrackedAdBanner> {
  bool _impressionTracked = false;

  @override
  Widget build(BuildContext context) {
    return VisibilityDetector(
      key: Key('ad_${widget.ad.id}'),
      onVisibilityChanged: (info) {
        // Track impression when ad is at least 50% visible
        if (info.visibleFraction >= widget.visibilityThreshold && !_impressionTracked) {
          _trackImpression();
        }
      },
      child: GestureDetector(
        onTap: () {
          _trackClick();
          widget.onTap?.call();
        },
        child: _buildAdContent(),
      ),
    );
  }

  Widget _buildAdContent() {
    return Container(
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(12),
        child: Stack(
          children: [
            // Ad image
            Image.network(
              widget.ad.imageUrl,
              fit: BoxFit.cover,
              width: double.infinity,
              height: 200,
              errorBuilder: (context, error, stackTrace) {
                return Container(
                  height: 200,
                  color: Colors.grey[300],
                  child: const Icon(Icons.image_not_supported),
                );
              },
            ),
            
            // CTA Button (if available)
            if (widget.ad.ctaText != null)
              Positioned(
                bottom: 16,
                right: 16,
                child: ElevatedButton(
                  onPressed: () {
                    _trackClick();
                    widget.onTap?.call();
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: _getCtaColor(widget.ad.ctaStyle),
                  ),
                  child: Text(widget.ad.ctaText!),
                ),
              ),
          ],
        ),
      ),
    );
  }

  void _trackImpression() {
    if (_impressionTracked) return;
    
    widget.analyticsService.trackImpression(
      adId: widget.ad.id,
      placement: widget.placement,
    );
    
    setState(() {
      _impressionTracked = true;
    });
  }

  void _trackClick() {
    widget.analyticsService.trackClick(
      adId: widget.ad.id,
      placement: widget.placement,
    );
  }

  Color _getCtaColor(String? style) {
    switch (style) {
      case 'primary':
        return Colors.blue;
      case 'secondary':
        return Colors.grey;
      case 'success':
        return Colors.green;
      case 'danger':
        return Colors.red;
      case 'warning':
        return Colors.orange;
      case 'info':
        return Colors.cyan;
      default:
        return Colors.blue;
    }
  }
}
```

---

## 3. Create Ad Model

```dart
// lib/models/ad_model.dart

class AdModel {
  final int id;
  final String title;
  final String? description;
  final String imageUrl;
  final String? ctaText;
  final String? ctaAction;
  final String ctaStyle;
  final Map<String, dynamic>? analyticsMeta;

  AdModel({
    required this.id,
    required this.title,
    this.description,
    required this.imageUrl,
    this.ctaText,
    this.ctaAction,
    this.ctaStyle = 'primary',
    this.analyticsMeta,
  });

  factory AdModel.fromJson(Map<String, dynamic> json) {
    return AdModel(
      id: json['id'],
      title: json['title'],
      description: json['description'],
      imageUrl: json['image_url'] ?? json['image_path'] ?? '',
      ctaText: json['cta_text'],
      ctaAction: json['cta_action'],
      ctaStyle: json['cta_style'] ?? 'primary',
      analyticsMeta: json['analytics_meta'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'title': title,
      'description': description,
      'image_url': imageUrl,
      'cta_text': ctaText,
      'cta_action': ctaAction,
      'cta_style': ctaStyle,
      'analytics_meta': analyticsMeta,
    };
  }
}
```

---

## 4. Usage Examples

### Example 1: Home Screen Banner

```dart
// In your home screen

class HomeScreen extends StatelessWidget {
  final AdAnalyticsService analyticsService;
  final List<AdModel> featuredAds;

  const HomeScreen({
    Key? key,
    required this.analyticsService,
    required this.featuredAds,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Home')),
      body: SingleChildScrollView(
        child: Column(
          children: [
            // Featured Ad Banner
            if (featuredAds.isNotEmpty)
              TrackedAdBanner(
                ad: featuredAds.first,
                placement: 'home_banner',
                analyticsService: analyticsService,
                onTap: () {
                  // Navigate to product page
                  _handleAdTap(context, featuredAds.first);
                },
              ),
            
            // Other content...
            const SizedBox(height: 16),
            _buildPackagesList(),
          ],
        ),
      ),
    );
  }

  void _handleAdTap(BuildContext context, AdModel ad) {
    if (ad.ctaAction != null) {
      // Navigate based on CTA action
      // Example: /packages/123 or /hotels/456
      Navigator.pushNamed(context, ad.ctaAction!);
    }
  }

  Widget _buildPackagesList() {
    // Your packages list implementation
    return Container();
  }
}
```

### Example 2: Search Results with Ads

```dart
// In your search results screen

class SearchResultsScreen extends StatelessWidget {
  final AdAnalyticsService analyticsService;
  final List<AdModel> sponsoredAds;
  final List<PackageModel> searchResults;

  const SearchResultsScreen({
    Key? key,
    required this.analyticsService,
    required this.sponsoredAds,
    required this.searchResults,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return ListView.builder(
      itemCount: searchResults.length + sponsoredAds.length,
      itemBuilder: (context, index) {
        // Show sponsored ad every 5 results
        if (index % 5 == 0 && index ~/ 5 < sponsoredAds.length) {
          return Padding(
            padding: const EdgeInsets.all(8.0),
            child: TrackedAdBanner(
              ad: sponsoredAds[index ~/ 5],
              placement: 'search_results',
              analyticsService: analyticsService,
              onTap: () {
                _handleAdTap(context, sponsoredAds[index ~/ 5]);
              },
            ),
          );
        }
        
        // Show regular search result
        final resultIndex = index - (index ~/ 5);
        return _buildSearchResultCard(searchResults[resultIndex]);
      },
    );
  }

  void _handleAdTap(BuildContext context, AdModel ad) {
    // Handle ad tap
  }

  Widget _buildSearchResultCard(PackageModel package) {
    // Your package card implementation
    return Container();
  }
}
```

### Example 3: Batch Tracking Multiple Ads

```dart
// When displaying multiple ads at once (e.g., carousel)

class AdCarousel extends StatefulWidget {
  final List<AdModel> ads;
  final AdAnalyticsService analyticsService;
  final String placement;

  const AdCarousel({
    Key? key,
    required this.ads,
    required this.analyticsService,
    required this.placement,
  }) : super(key: key);

  @override
  State<AdCarousel> createState() => _AdCarouselState();
}

class _AdCarouselState extends State<AdCarousel> {
  bool _batchTracked = false;

  @override
  void initState() {
    super.initState();
    _trackBatchImpressions();
  }

  void _trackBatchImpressions() {
    if (_batchTracked) return;

    final impressions = widget.ads.map((ad) => {
      'ad_id': ad.id,
      'placement': widget.placement,
    }).toList();

    widget.analyticsService.trackImpressionsBatch(impressions);
    
    setState(() {
      _batchTracked = true;
    });
  }

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 200,
      child: PageView.builder(
        itemCount: widget.ads.length,
        itemBuilder: (context, index) {
          return GestureDetector(
            onTap: () {
              widget.analyticsService.trackClick(
                adId: widget.ads[index].id,
                placement: widget.placement,
              );
              // Handle navigation
            },
            child: Image.network(
              widget.ads[index].imageUrl,
              fit: BoxFit.cover,
            ),
          );
        },
      ),
    );
  }
}
```

---

## 5. Dependency Setup

Add required dependencies to `pubspec.yaml`:

```yaml
dependencies:
  http: ^1.1.0
  visibility_detector: ^0.4.0+2
```

Run:
```bash
flutter pub get
```

---

## 6. Service Initialization

Initialize the analytics service in your app:

```dart
// In your main.dart or app initialization

import 'package:flutter/material.dart';
import 'services/ad_analytics_service.dart';

class MyApp extends StatefulWidget {
  @override
  State<MyApp> createState() => _MyAppState();
}

class _MyAppState extends State<MyApp> {
  late final AdAnalyticsService _analyticsService;

  @override
  void initState() {
    super.initState();
    _analyticsService = AdAnalyticsService(
      baseUrl: 'https://your-api-url.com',
    );
  }

  @override
  void dispose() {
    _analyticsService.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      home: HomeScreen(
        analyticsService: _analyticsService,
      ),
    );
  }
}
```

---

## 7. Testing

### Test Impression Tracking
```dart
void testImpressionTracking() async {
  final service = AdAnalyticsService(
    baseUrl: 'http://localhost:8000',
  );

  final result = await service.trackImpression(
    adId: 1,
    placement: 'test',
  );

  print('Impression tracked: $result');
}
```

### Test Click Tracking
```dart
void testClickTracking() async {
  final service = AdAnalyticsService(
    baseUrl: 'http://localhost:8000',
  );

  final result = await service.trackClick(
    adId: 1,
    placement: 'test',
  );

  print('Click tracked: $result');
}
```

---

## 8. Best Practices

### DO:
✅ Use VisibilityDetector to track impressions only when ad is visible  
✅ Implement local caching to prevent duplicate tracking  
✅ Use batch tracking for multiple ads  
✅ Handle errors gracefully (don't block UI)  
✅ Use timeouts for network requests  
✅ Track device type and placement for better insights  

### DON'T:
❌ Track impression on widget build (use visibility detection)  
❌ Block UI thread waiting for tracking response  
❌ Track every scroll event (use debouncing)  
❌ Send PII in tracking data  
❌ Track impressions for off-screen ads  

---

## 9. Performance Considerations

1. **Network Efficiency**
   - Use batch tracking for multiple ads
   - Fire-and-forget approach (don't wait for response)
   - Implement exponential backoff for retries

2. **Memory Management**
   - Clear tracking cache periodically
   - Dispose services when not needed
   - Use weak references for ad widgets

3. **Battery Optimization**
   - Batch network requests
   - Use background tasks for non-critical tracking
   - Respect device power saving mode

---

## 10. Troubleshooting

### Issue: Impressions not tracked
- Check if ad is visible on screen (use VisibilityDetector)
- Verify API URL is correct
- Check network connectivity
- Look for console errors

### Issue: Duplicate tracking
- Ensure cache duration is appropriate (5 minutes default)
- Don't clear cache too frequently
- Use unique keys for VisibilityDetector

### Issue: High network usage
- Use batch tracking for multiple ads
- Increase cache duration
- Implement proper visibility thresholds

---

## Conclusion

The Flutter integration is now ready to track ad impressions and clicks efficiently while respecting user privacy and device resources. The implementation uses best practices for performance and reliability.

**Status**: ✅ Ready for Integration  
**Version**: 1.0  
**Last Updated**: 2025-11-08
