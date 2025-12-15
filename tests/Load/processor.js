/**
 * Artillery Processor
 * 
 * Custom functions for load testing
 * Data preparation, response processing, assertions
 */

// Helper functions for load testing
module.exports = {
  /**
   * Before request - set up data
   */
  setupRequest: function(requestParams, context, ee, next) {
    // Add timestamp to requests
    requestParams.headers = requestParams.headers || {};
    requestParams.headers['X-Request-ID'] = Math.random().toString(36).substring(7);
    requestParams.headers['User-Agent'] = 'Telemedicine Load Test';
    
    return next();
  },

  /**
   * After response - validate and process
   */
  validateResponse: function(requestParams, response, context, ee, next) {
    // Check response time
    const responseTime = response.headers['x-response-time'];
    if (responseTime && parseInt(responseTime) > 1000) {
      ee.emit('customStat', {
        stat: 'slow_response',
        value: 1
      });
    }

    // Check for errors in response
    if (response.statusCode >= 400) {
      ee.emit('customStat', {
        stat: 'error_response',
        value: 1
      });
    }

    return next();
  },

  /**
   * Generate random email
   */
  generateEmail: function(context, ee, next) {
    const timestamp = Date.now();
    const random = Math.random().toString(36).substring(7);
    context.vars.random_email = `test_${timestamp}_${random}@example.com`;
    return next();
  },

  /**
   * Generate random appointment slot
   */
  generateSlot: function(context, ee, next) {
    const hours = Math.floor(Math.random() * 8) + 9; // 9-17
    const minutes = Math.random() > 0.5 ? '00' : '30';
    context.vars.random_slot = `${String(hours).padStart(2, '0')}:${minutes}`;
    return next();
  },

  /**
   * Check for business logic errors
   */
  checkBusinessLogic: function(requestParams, response, context, ee, next) {
    if (response.body) {
      try {
        const body = JSON.parse(response.body);
        
        // Check for specific error messages
        if (body.message && body.message.includes('double booking')) {
          ee.emit('customStat', {
            stat: 'double_booking_error',
            value: 1
          });
        }
        
        if (body.message && body.message.includes('slot unavailable')) {
          ee.emit('customStat', {
            stat: 'slot_unavailable_error',
            value: 1
          });
        }
      } catch (e) {
        // JSON parse error, ignore
      }
    }

    return next();
  }
};
