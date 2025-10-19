import React from 'react';

export default function MapCard({ title, author }) {
    return (
        <div className="map-card">
            <h3 style={{ margin: 0 }}>{title}</h3>
            <p>Créé par {author}</p>
        </div>
    )
}
