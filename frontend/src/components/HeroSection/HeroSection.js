import React from 'react';
import './HeroSection.css';

const HeroSection = () => {
  return (
    <section className="hero">
      <div className="hero__content">
        <h1 className="hero__title">Discover Amazing Places</h1>
        <p className="hero__subtitle">Join our community and share your travel experiences.</p>
        <button className="hero__button">Get Started</button>
      </div>
    </section>
  );
};

export default HeroSection;