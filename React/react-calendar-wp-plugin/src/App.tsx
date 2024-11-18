import React, { useState } from 'react';
import './App.css';
import Calendar from './components/react-calendar/Calendar.tsx';

function App() {
  const [currentDate, setCurrentDate] = useState(new Date());
  const [events, setEvents] = useState<CalendarEvent[]>();

  interface CalendarEvent {
    id: number;
    name: string;
    date: Date;
    link: string;
  }

  interface CustomEvent {
    id: string | number;
    name: string;
    date: Date;
    link: string;
  }

  return (
    <div className="wp-react-calendar">
      <Calendar 
        currentDate={currentDate}
        onNavigate={(date: Date) => setCurrentDate(date)}
        events={events as CalendarEvent[]}
        onEventAdd={(event: CustomEvent) => {
          const calendarEvent: CalendarEvent = {
            id: Number(event.id),
            name: event.name,
            date: event.date,
            link: event.link,
          };
          setEvents(prev => [...(prev || []), calendarEvent]);
        }}
        onEventEdit={(event: CustomEvent) => {
          const calendarEvent: CalendarEvent = {
            id: Number(event.id),
            name: event.name,
            date: event.date,
            link: event.link,
          };
          setEvents(prev => 
            prev?.map(e => e.id === calendarEvent.id ? calendarEvent : e)
          );
        }}
      />
    </div>
  );
}

export default App;