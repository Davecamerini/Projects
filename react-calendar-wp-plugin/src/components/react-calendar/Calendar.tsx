import React, { useState, useEffect } from 'react';
import './Calendar.css';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faArrowLeft, faArrowRight } from '@fortawesome/free-solid-svg-icons';

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
  const [cellBgColor, setCellBgColor] = useState<string>('#FFFFFF'); // Default cell color
  const [headerColor, setHeaderColor] = useState<string>('#0073aa'); // Default header color
  const [eventPillBgColor, setEventPillBgColor] = useState<string>('#FFD700'); // Default event pill color

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
      setCellBgColor(data.calendar_cell_bg_color || '#FFFFFF'); // New cell background color
      setHeaderColor(data.calendar_header_color || '#0073aa'); // New header color
      setEventPillBgColor(data.event_pill_bg_color || '#FFD700'); // New event pill background color
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
        <div key={`day-${day}`} className="calendar-cell" style={{ backgroundColor: cellBgColor }}>
          <div className="day-number">{day}</div>
          {dayEvents.map(event => (
            <div key={event.id} className="event-pill" style={{ backgroundColor: eventPillBgColor }}>
              <a href={event.link} rel="noopener noreferrer">
                {event.name}
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
      <div className="calendar-header" style={{ backgroundColor: headerColor }}>
        <button className="button" style={{ backgroundColor: buttonBgColor }} onClick={handlePrevMonth}>
          <FontAwesomeIcon icon={faArrowLeft} />
        </button>
        <h2>{currentDateState.toLocaleString('default', { month: 'long', year: 'numeric' })}</h2>
        <button className="button" style={{ backgroundColor: buttonBgColor }} onClick={handleNextMonth}>
          <FontAwesomeIcon icon={faArrowRight} />
        </button>
      </div>
      <div className="calendar-weekdays">
        {['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].map(day => (
          <div key={day} className="weekday">{day}</div>
        ))}
      </div>
      <div className="calendar-grid">
        {renderCalendarGrid()}
      </div>
    </div>
  );
};

export default Calendar;
