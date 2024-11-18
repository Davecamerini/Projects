import React, { useState, useEffect } from 'react';
import './Calendar.css';

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

  useEffect(() => {
    fetchEvents();
  }, []);

  const fetchEvents = async () => {
    try {
      const response = await fetch('/wp-json/wp-react-calendar/v1/events');
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

    const days = [];
    
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
        <div key={`day-${day}`} className="calendar-cell">
          <div className="day-number">{day}</div>
          {dayEvents.map(event => (
            <div key={event.id} className="event-pill">
              <a href={event.link} target="_blank" rel="noopener noreferrer">
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
    <div className="calendar">
      <div className="calendar-header">
        <button onClick={handlePrevMonth}>&lt;</button>
        <h2>{currentDateState.toLocaleString('default', { month: 'long', year: 'numeric' })}</h2>
        <button onClick={handleNextMonth}>&gt;</button>
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
