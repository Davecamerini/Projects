import React, { useState, useEffect } from 'react';
import './EventForm.css';

interface EventFormProps {
  selectedDate: Date;
  onSubmit: (event: Event) => void;
  onClose: () => void;
  editEvent?: Event;
}

interface Event {
  id: string;
  name: string;
  date: Date;
  link: string;
}

const EventForm: React.FC<EventFormProps> = ({
  selectedDate,
  onSubmit,
  onClose,
  editEvent
}) => {
  const [formData, setFormData] = useState({
    name: '',
    link: '',
  });

  useEffect(() => {
    if (editEvent) {
      setFormData({
        name: editEvent.name,
        link: editEvent.link,
      });
    }
  }, [editEvent]);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const newEvent: Event = {
      id: editEvent?.id || crypto.randomUUID(),
      name: formData.name,
      date: selectedDate,
      link: formData.link,
    };
    onSubmit(newEvent);
    onClose();
  };

  return (
    <div className="event-form-overlay">
      <div className="event-form">
        <h3>{editEvent ? 'Edit Event' : 'Add New Event'}</h3>
        <form onSubmit={handleSubmit}>
          <div className="form-group">
            <label htmlFor="name">Event Name:</label>
            <input
              type="text"
              id="name"
              value={formData.name}
              onChange={(e) => setFormData({ ...formData, name: e.target.value })}
              required
            />
          </div>
          <div className="form-group">
            <label htmlFor="link">Link:</label>
            <input
              type="url"
              id="link"
              value={formData.link}
              onChange={(e) => setFormData({ ...formData, link: e.target.value })}
              required
            />
          </div>
          <div className="form-group">
            <label>Date:</label>
            <div>{selectedDate.toLocaleDateString()}</div>
          </div>
          <div className="form-actions">
            <button type="submit">{editEvent ? 'Update' : 'Create'}</button>
            <button type="button" onClick={onClose}>Cancel</button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default EventForm; 