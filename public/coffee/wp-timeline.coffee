class @Timeline

  constructor: (options)->
    @posts = options.posts
    @totalMonths = options.totalMonths
    @eventsInMonth = {}
    @dims = 
      monthHeight: 100/@totalMonths
      eventWidth: 20
    @tooltip = $('#wp-timeline-tooltip')
    @init()

  init: ->
    console.log '[WP-Timeline] Initialising...'
    @addEventListeners()
    @addEvents()

  addEventListeners: ->
    that = this
    $(document).on 'mouseover', '.event-marker', ->
      el = $(this)
      text = el.attr 'title'
      that.tooltip.text(text).css(
        top : el.data('top')
        right : el.data('right')
        display: 'inline-block'
      )
    $(document).on 'mouseleave', '.event-marker', ->
      that.tooltip.hide()
    
  addEvents: ->
    #console.log @posts
    $.each(@posts, (i, el)=>
      @eventsInMonth[el.monthsFromNow] = @eventsInMonth[el.monthsFromNow]+1 || 1
      posTop = @dims.monthHeight*(el.monthsFromNow-1) + '%'
      posRight = @dims.eventWidth*@eventsInMonth[el.monthsFromNow]
      $('<a/>', 
          class: 'event-marker ' + el.post_type
          href: el.url
          title: el.title
          'data-top': posTop
          'data-right': posRight
      ).css(
        top    : posTop
        right  : posRight
        height : 'calc(' + @dims.monthHeight*el.totalMonths + '% - 8px)'
      ).appendTo('#wp-timeline')
      #$event = '<a href="' + el.url +'"" class="event-marker" data-post-type="" title="' + el.title + '"></a>';
    )