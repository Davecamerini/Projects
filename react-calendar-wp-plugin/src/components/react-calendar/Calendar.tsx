import React, { useState, useEffect } from 'react';
import './Calendar.css';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faArrowLeft, faArrowRight } from '@fortawesome/free-solid-svg-icons';
import { ReactComponent as VETTORIAIND } from './AIND.svg';

const AIND = ({size = 16, color = '#000'}) => {
  return (
    <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100%', margin: '5px 0 5px 0' }}>
      <VETTORIAIND width={size} height={size} fill={color} />
    </div>
  )
}

interface Event {
  id: number;
  name: string;
  date: string;
  link: string;
}

interface CalendarProps {
  currentDate: Date;
  onNavigate: (date: Date) => void;
  events?: Event[];
  onEventAdd: (event: Event) => void;
  onEventEdit: (event: Event) => void;
}

const Calendar: React.FC<CalendarProps> = ({ currentDate, onNavigate, events, onEventAdd, onEventEdit }) => {
  const [currentDateState, setCurrentDateState] = useState(currentDate);
  const [eventsState, setEventsState] = useState<Event[]>(events || []);
  const [bgColor, setBgColor] = useState<string>('#F20000'); // Default color
  const [buttonBgColor, setButtonBgColor] = useState<string>('darkred'); // Default button color
  const [buttonHoverBgColor, setButtonHoverBgColor] = useState<string>('#FF4500'); // Default button hover color
  const [cellBgColor, setCellBgColor] = useState<string>('#FFFFFF'); // Default cell color
  const [cellHoverBgColor, setCellHoverBgColor] = useState<string>('#F0F0F0'); // Default cell hover color
  const [headerColor, setHeaderColor] = useState<string>('#F20000'); // Default header color
  const [eventPillBgColor, setEventPillBgColor] = useState<string>('#FFD700'); // Default event pill color
  const [eventPillTextColor, setEventPillTextColor] = useState<string>('#000000'); // Default event pill text color
  const [weekdayBgColor, setWeekdayBgColor] = useState<string>('#FFFFFF'); // Default weekday color
  const [weekdayTextColor, setWeekdayTextColor] = useState<string>('#FFFFFF'); // Default weekday text color
  const [headerTextColor, setHeaderTextColor] = useState<string>('#FFFFFF'); // Default header text color

  useEffect(() => {
    fetchEvents('/wp-json/wp-react-calendar/v1/events');
    fetchStyles(); // Fetch styles from the server
  }, []);

  const fetchStyles = async () => {
    try {
      const response = await fetch('/wp-json/wp-react-calendar/v1/styles');
      const data = await response.json();
      setBgColor(data.calendar_bg_color || '#F20000');
      setButtonBgColor(data.button_bg_color || 'darkred');
      setButtonHoverBgColor(data.button_hover_bg_color || '#FF4500'); // New hover color
      setCellBgColor(data.calendar_cell_bg_color || '#FFFFFF'); // New cell background color
      setCellHoverBgColor(data.calendar_cell_hover_bg_color || '#F0F0F0'); // New cell hover color
      setHeaderColor(data.calendar_header_color || '#0073aa'); // New header color
      setEventPillBgColor(data.event_pill_bg_color || '#FFD700'); // New event pill background color
      setEventPillTextColor(data.event_pill_text_color || '#000000'); // New event pill text color
      setWeekdayBgColor(data.weekday_cell_bg_color || '#F0F0F0'); // New weekday background color
      setWeekdayTextColor(data.weekday_text_color || '#FFFFFF'); // New weekday text color
      setHeaderTextColor(data.header_text_color || '#FFFFFF'); // New header text color
    } catch (error) {
      console.error('Error fetching styles:', error);
    }
  };

  const fetchEvents = async (url: string) => {
    try {
      const response = await fetch(url);
      const data = await response.json();

      console.log('Fetched events:', data);

      if (Array.isArray(data)) {
        setEventsState(data);
      } else {
        console.error('Fetched data is not an array:', data);
        setEventsState([]);
      }
    } catch (error) {
      console.error('Error fetching events:', error);
      setEventsState([]);
    }
  };

  const getDaysInMonth = (date: Date) => {
    return new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
  };

  const handlePrevMonth = () => {
    setCurrentDateState(new Date(currentDateState.setMonth(currentDateState.getMonth() - 1)));
  };

  const handleNextMonth = () => {
    setCurrentDateState(new Date(currentDateState.setMonth(currentDateState.getMonth() + 1)));
  };

  const renderCalendarGrid = () => {
    const daysInMonth = getDaysInMonth(currentDateState);
    const firstDayOfMonth = new Date(currentDateState.getFullYear(), currentDateState.getMonth(), 1);
    const startingDay = firstDayOfMonth.getDay();

    const days: JSX.Element[] = [];
    
    // Add empty cells for days before the first day of the month
    for (let i = 0; i < startingDay; i++) {
      days.push(<div key={`empty-${i}`} className="calendar-cell empty"></div>);
    }

    // Add cells for each day of the month
    for (let day = 1; day <= daysInMonth; day++) {
      const date = new Date(currentDateState.getFullYear(), currentDateState.getMonth(), day);
      const dayEvents: Event[] = eventsState.filter(event => 
        new Date(event.date).toDateString() === date.toDateString()
      );

      days.push(
        <div
          key={`day-${day}`}
          className="calendar-cell"
          style={{ backgroundColor: cellBgColor }}
          onMouseEnter={(e) => (e.currentTarget.style.backgroundColor = cellHoverBgColor)}
          onMouseLeave={(e) => (e.currentTarget.style.backgroundColor = cellBgColor)}
        >
          <div className="day-number">{day}</div>
          {dayEvents.map(event => (
            <div key={event.id} className="event-pill" style={{ backgroundColor: eventPillBgColor, color: eventPillTextColor }}>
              <a href={event.link} rel="noopener noreferrer" style={{ color: eventPillTextColor }}>
                <AIND size={50} color={'#000000'} />
              </a>
            </div>
          ))}
        </div>
      );
    }

    return days;
  };

  return (
    <div className="calendar" style={{ backgroundColor: bgColor }}>
      <div className="calendar-header" style={{ backgroundColor: headerColor, color: headerTextColor }}>
        <button
          className="button"
          style={{ backgroundColor: buttonBgColor }}
          onMouseEnter={(e) => (e.currentTarget.style.backgroundColor = buttonHoverBgColor)}
          onMouseLeave={(e) => (e.currentTarget.style.backgroundColor = buttonBgColor)}
          onClick={handlePrevMonth}
        >
          <FontAwesomeIcon icon={faArrowLeft} />
        </button>
        <h2 style={{ color: headerTextColor }}>{currentDateState.toLocaleString('default', { month: 'long', year: 'numeric' })}</h2>
        <button
          className="button"
          style={{ backgroundColor: buttonBgColor }}
          onMouseEnter={(e) => (e.currentTarget.style.backgroundColor = buttonHoverBgColor)}
          onMouseLeave={(e) => (e.currentTarget.style.backgroundColor = buttonBgColor)}
          onClick={handleNextMonth}
        >
          <FontAwesomeIcon icon={faArrowRight} />
        </button>
      </div>
      <div className="calendar-weekdays">
        {['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].map(day => (
          <div key={day} className="weekday" style={{ backgroundColor: weekdayBgColor, color: weekdayTextColor }}>{day}</div>
        ))}
      </div>
      <div className="calendar-grid">
        {renderCalendarGrid()}
      </div>
    </div>
  );
};

export default Calendar;
