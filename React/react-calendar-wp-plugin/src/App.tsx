import React, { useState } from 'react';
import './App.css';
import Calendar from './components/react-calendar/Calendar.tsx';

function App() {
  const [currentDate, setCurrentDate] = useState(new Date());
  const [events, setEvents] = useState<Event[]>();

  interface Event {
    id: string;
    name: string;
    date: Date;
    link: string;
  }

  return (
    <div className="wp-react-calendar">
      <Calendar 
        currentDate={currentDate}
        onNavigate={(date: Date) => setCurrentDate(date)}
        events={events}
        onEventAdd={(event: Event) => setEvents(prev => [...(prev || []), event])}
        onEventEdit={(event: Event) => {
          setEvents(prev => 
            prev?.map(e => e.id === event.id ? event : e)
          );
        }}
      />
    </div>
  );
}

export default App;