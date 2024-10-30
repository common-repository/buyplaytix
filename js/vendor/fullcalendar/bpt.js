/*!
 * FullCalendar v1.6.3 Google Calendar Plugin
 * Docs & License: http://arshaw.com/fullcalendar/
 * (c) 2013 Adam Shaw
 */

(function($) {

	var fc = $.fullCalendar;
	var formatDate = fc.formatDate;
	var parseISO8601 = fc.parseISO8601;
	var addDays = fc.addDays;
	var applyAll = fc.applyAll;

	fc.sourceNormalizers
			.push(function(sourceOptions) {
				if (sourceOptions.dataType == 'bpt'
						|| sourceOptions.dataType === undefined
						&& (sourceOptions.url || '')
								.match(/.*calendar.service$/)) {
					sourceOptions.dataType = 'bpt';
					if (sourceOptions.editable === undefined) {
						sourceOptions.editable = false;
					}
				}
			});

	fc.sourceFetchers.push(function(sourceOptions, start, end) {
		if (sourceOptions.dataType == 'bpt') {
			return transformOptions(sourceOptions, start, end);
		}
	});

	function transformOptions(sourceOptions, start, end) {

		var success = sourceOptions.success;
		var metro = $('#metro').val();
		
		var data = $.extend({}, sourceOptions.data || {}, {
			'metro': metro,
			'start-min' : formatDate(start, 'u'),
			'start-max' : formatDate(end, 'u'),
			'singleevents' : true,
			'max-results' : 9999
		});

		var ctz = sourceOptions.currentTimezone;
		if (ctz) {
			data.ctz = ctz = ctz.replace(' ', '_');
		}

		return $.extend({}, sourceOptions, {
			url : sourceOptions.url,
			dataType : 'jsonp',
			data : data,
			startParam : false,
			endParam : false,
			success : function(data) {
				
				
				var events = [];
				
				if(data.metros && Object.keys(data.metros).length > 0) {
					var metro = $('#metro');
					if(metro.length == 0) {
						var headerLeft = $('td.fc-header-left');
						headerLeft.append($('<br /><select id="metro" name="metro"></select>'));
						metro = $('#metro');
						
						$('#metro').bind("change.metro", function() {
							$('#calendar').fullCalendar('refetchEvents');
						});
					} else {
						$('#metro').empty();
				    }
					$('#metro')
			          .append($('<option>', { value : 'all' })
			          .text('All')); 										
					$.each(data.metros, function(i, entry) {
						 $('#metro')
				          .append($('<option>', { value : i })
				          .text(entry)); 					
					});
					$('#metro').val(data.metro);
				}

				$.each(data.events, function(i, entry) {
					var startStr = entry.startStr;
					var start = parseISO8601(startStr, true);
					entry.start = start;
					entry.end = new Date(start.getTime());
					entry.end.setHours(entry.end.getHours() + 2);

					events.push(entry);
				});

				return events;
			}
		});

	}

	// legacy
	fc.bptFeed = function(url, sourceOptions) {
		return $.extend({}, sourceOptions, {
			url : url,
			dataType : 'bpt'
		});
	};

})(jQuery);
