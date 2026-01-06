/**
 * Brain Web Analytics - Lightweight Privacy-First Analytics
 *
 * Tracks pageviews, user actions, scroll depth, and Web Vitals.
 * Sends data to Brain Nucleus API.
 *
 * Usage:
 *   BrainAnalytics.init({ url: 'https://brain.example.com', key: 'your-api-key' });
 *
 * @version 1.0.0
 */
/* eslint-disable @typescript-eslint/no-this-alias, @typescript-eslint/no-unused-vars */
(function (window, document) {
  'use strict';

  var BrainAnalytics = {
    config: {
      url: '',
      key: '',
      trackScrollDepth: true,
      trackPerformance: true,
      trackClicks: true,
      debug: false,
    },
    sessionId: null,
    pageCount: 0,
    scrollDepths: [],
    startTime: Date.now(),

    /**
     * Initialize Brain Analytics
     * @param {Object} options - Configuration options
     */
    init: function (options) {
      if (!options.url || !options.key) {
        console.warn('[BrainAnalytics] Missing url or key');
        return;
      }

      // Respect Do Not Track
      if (navigator.doNotTrack === '1') {
        if (options.debug) console.log('[BrainAnalytics] DNT enabled, not tracking');
        return;
      }

      // Merge options
      for (var key in options) {
        if (options.hasOwnProperty(key)) {
          this.config[key] = options[key];
        }
      }

      // Generate or retrieve session ID
      this.sessionId = this.getSessionId();
      this.pageCount = this.getPageCount();

      // Track pageview
      this.trackPageview();

      // Set up event listeners
      if (this.config.trackClicks) this.setupClickTracking();
      if (this.config.trackScrollDepth) this.setupScrollTracking();
      if (this.config.trackPerformance) this.trackPerformance();

      // Track time on page when leaving
      this.setupExitTracking();

      if (this.config.debug) console.log('[BrainAnalytics] Initialized', this.config);
    },

    /**
     * Get or create session ID
     */
    getSessionId: function () {
      try {
        var stored = sessionStorage.getItem('brain_session_id');
        if (stored) {
          return stored;
        }
        var newId = 'brain_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        sessionStorage.setItem('brain_session_id', newId);
        return newId;
      } catch (e) {
        // Fallback if sessionStorage not available
        return 'brain_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
      }
    },

    /**
     * Get or increment page count
     */
    getPageCount: function () {
      try {
        var stored = sessionStorage.getItem('brain_page_count');
        var count = stored ? parseInt(stored, 10) + 1 : 1;
        sessionStorage.setItem('brain_page_count', count.toString());
        return count;
      } catch (e) {
        return 1;
      }
    },

    /**
     * Get device info
     */
    getDeviceInfo: function () {
      var ua = navigator.userAgent;
      var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(ua);
      var isTablet = /iPad|Android/i.test(ua) && !isMobile;
      return {
        type: isMobile ? 'mobile' : isTablet ? 'tablet' : 'desktop',
        browser: this.getBrowser(),
        screen: {
          width: window.screen.width,
          height: window.screen.height,
        },
      };
    },

    /**
     * Get browser name
     */
    getBrowser: function () {
      var ua = navigator.userAgent;
      if (ua.indexOf('Firefox') > -1) return 'Firefox';
      if (ua.indexOf('Chrome') > -1 && ua.indexOf('Edg') === -1) return 'Chrome';
      if (ua.indexOf('Safari') > -1 && ua.indexOf('Chrome') === -1) return 'Safari';
      if (ua.indexOf('Edg') > -1) return 'Edge';
      return 'Unknown';
    },

    /**
     * Get UTM parameters
     */
    getUtmParams: function () {
      var params = new URLSearchParams(window.location.search);
      var utm = {};
      ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'].forEach(function (key) {
        var value = params.get(key);
        if (value) {
          utm[key] = value;
        }
      });
      return Object.keys(utm).length > 0 ? utm : null;
    },

    /**
     * Track pageview
     */
    trackPageview: function () {
      var device = this.getDeviceInfo();
      var utmParams = this.getUtmParams();
      var landingPage = this.pageCount === 1 ? location.pathname : null;

      var payload = {
        url: location.pathname + location.search,
        title: document.title,
        referrer: document.referrer || null,
        device: device.type,
        browser: device.browser,
        screen: device.screen,
        session_id: this.sessionId,
        page_count: this.pageCount,
        landing_page: landingPage,
      };

      if (utmParams) {
        payload.utm = utmParams;
      }

      this.send('web.pageview', payload);
    },

    /**
     * Setup click tracking
     */
    setupClickTracking: function () {
      var self = this;
      document.addEventListener(
        'click',
        function (e) {
          var target = e.target;
          while (target && target.tagName !== 'A') {
            target = target.parentElement;
          }

          if (!target || !target.href) {
            return;
          }

          var href = target.href;
          var isExternal = href.indexOf(window.location.origin) === -1;
          var isPhone = href.indexOf('tel:') === 0;
          var isEmail = href.indexOf('mailto:') === 0;
          var isQuote = target.classList.contains('quote-link') || target.textContent.toLowerCase().includes('quote');

          if (isQuote) {
            var device = self.getDeviceInfo();
            var utmParams = self.getUtmParams();
            var quotePayload = {
              url: location.pathname + location.search,
              target_url: href,
              referrer: document.referrer || null,
              device: device.type,
              browser: device.browser,
              screen: device.screen,
              session_id: self.sessionId,
              landing_page: self.pageCount === 1 ? location.pathname : null,
            };

            if (utmParams) {
              quotePayload.utm = utmParams;
            }

            self.send('web.quote_clicked', quotePayload);
          } else if (isPhone) {
            self.send('web.phone_clicked', {
              url: location.pathname,
              phone: href.replace('tel:', ''),
            });
          } else if (isExternal) {
            self.send('web.external_link_clicked', {
              url: location.pathname,
              target_url: href,
            });
          }
        },
        true
      );
    },

    /**
     * Setup scroll tracking
     */
    setupScrollTracking: function () {
      var self = this;
      var depths = [25, 50, 75, 100];
      var tracked = [];

      window.addEventListener(
        'scroll',
        function () {
          var scrollPercent = Math.round(((window.scrollY + window.innerHeight) / document.documentElement.scrollHeight) * 100);

          depths.forEach(function (depth) {
            if (scrollPercent >= depth && tracked.indexOf(depth) === -1) {
              tracked.push(depth);
              self.send('web.scroll_depth', {
                url: location.pathname,
                depth: depth,
              });
            }
          });
        },
        { passive: true }
      );
    },

    /**
     * Track performance metrics
     */
    trackPerformance: function () {
      var self = this;

      if (!window.performance || !window.performance.timing) {
        return;
      }

      window.addEventListener('load', function () {
        setTimeout(function () {
          var timing = window.performance.timing;
          var navigation = window.performance.navigation;

          var metrics = {
            url: location.pathname,
            ttfb: timing.responseStart - timing.requestStart,
            dom_ready: timing.domContentLoadedEventEnd - timing.navigationStart,
            load_time: timing.loadEventEnd - timing.navigationStart,
          };

          // LCP if available
          if (window.PerformanceObserver) {
            try {
              var observer = new PerformanceObserver(function (list) {
                var entries = list.getEntries();
                var lastEntry = entries[entries.length - 1];
                metrics.lcp = Math.round(lastEntry.renderTime || lastEntry.loadTime);
                self.send('web.web_vitals', metrics);
                observer.disconnect();
              });
              observer.observe({ entryTypes: ['largest-contentful-paint'] });
            } catch (e) {
              // LCP not supported, send without it
              self.send('web.web_vitals', metrics);
            }
          } else {
            self.send('web.web_vitals', metrics);
          }
        }, 100);
      });
    },

    /**
     * Track time on page when exiting
     */
    setupExitTracking: function () {
      var self = this;

      var sendExitEvent = function () {
        var timeOnPage = Math.round((Date.now() - self.startTime) / 1000);
        if (timeOnPage > 0) {
          self.send('web.time_on_page', {
            url: location.pathname,
            seconds: timeOnPage,
          });
        }
      };

      // Use visibilitychange for more reliable tracking
      document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'hidden') {
          sendExitEvent();
        }
      });

      // Fallback for older browsers
      window.addEventListener('pagehide', sendExitEvent);
    },

    /**
     * Manual event tracking
     */
    track: function (eventType, payload) {
      this.send('web.' + eventType, payload || {});
    },

    /**
     * Send event to Brain API
     */
    send: function (eventType, payload) {
      if (!this.config.url || !this.config.key) {
        return;
      }

      var data = {
        type: eventType,
        payload: payload,
        timestamp: new Date().toISOString(),
      };

      if (this.config.debug) {
        console.log('[BrainAnalytics]', eventType, data);
      }

      // Use sendBeacon for reliability (especially on page unload)
      if (navigator.sendBeacon) {
        var blob = new Blob([JSON.stringify(data)], { type: 'application/json' });
        var url = this.config.url + '/api/events?key=' + encodeURIComponent(this.config.key);
        navigator.sendBeacon(url, blob);
      } else {
        // Fallback to fetch
        fetch(this.config.url + '/api/events?key=' + encodeURIComponent(this.config.key), {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(data),
          keepalive: true,
        }).catch(function (err) {
          if (self.config.debug) {
            console.error('[BrainAnalytics] Send failed:', err);
          }
        });
      }
    },
  };

  // Expose globally
  window.BrainAnalytics = BrainAnalytics;
})(window, document);

