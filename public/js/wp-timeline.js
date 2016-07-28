this.Timeline = (function() {
  function Timeline(options) {
    this.posts = options.posts;
    this.totalMonths = options.totalMonths;
    this.eventsInMonth = {};
    this.dims = {
      monthHeight: 100 / this.totalMonths,
      eventWidth: 20
    };
    this.tooltip = $('#wp-timeline-tooltip');
    this.init();
  }

  Timeline.prototype.init = function() {
    console.log('[WP-Timeline] Initialising...');
    this.addEventListeners();
    return this.addEvents();
  };

  Timeline.prototype.addEventListeners = function() {
    var that;
    that = this;
    $(document).on('mouseover', '.event-marker', function() {
      var el, text;
      el = $(this);
      text = el.attr('title');
      return that.tooltip.text(text).css({
        top: el.data('top'),
        right: el.data('right'),
        display: 'inline-block'
      });
    });
    return $(document).on('mouseleave', '.event-marker', function() {
      return that.tooltip.hide();
    });
  };

  Timeline.prototype.addEvents = function() {
    return $.each(this.posts, (function(_this) {
      return function(i, el) {
        var posRight, posTop;
        _this.eventsInMonth[el.monthsFromNow] = _this.eventsInMonth[el.monthsFromNow] + 1 || 1;
        posTop = _this.dims.monthHeight * (el.monthsFromNow - 1) + '%';
        posRight = _this.dims.eventWidth * _this.eventsInMonth[el.monthsFromNow];
        return $('<a/>', {
          "class": 'event-marker ' + el.post_type,
          href: el.url,
          title: el.title,
          'data-top': posTop,
          'data-right': posRight
        }).css({
          top: posTop,
          right: posRight,
          height: 'calc(' + _this.dims.monthHeight * el.totalMonths + '% - 8px)'
        }).appendTo('#wp-timeline');
      };
    })(this));
  };

  return Timeline;

})();
