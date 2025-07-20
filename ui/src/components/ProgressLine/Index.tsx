import React, { useEffect, useState, useRef } from "react";
import "./progressline.scss";

interface VisualPart {
  percentage: string;
  colorClass: string;
}

interface ProgressLineProps {
  label?: string;
  backgroundColor?: string;
  visualParts?: VisualPart[];
}

const ProgressLine: React.FC<ProgressLineProps> = ({
  label,
  backgroundColor = "#e5e5e5",
  visualParts = [
    {
      percentage: "0%",
      colorClass: "white"
    }
  ]
}) => {
  const [widths, setWidths] = useState<string[]>(visualParts.map(() => "50%"));
  const progressLineRef = useRef<HTMLDivElement | null>(null);

  useEffect(() => {
    const options = {
      root: null, // Use the viewport as the root
      rootMargin: "0px", // No margin
      threshold: 0.5, // Trigger when at least 50% of the element is in view
    };

    const handleIntersection = (entries: IntersectionObserverEntry[]) => {
      if (entries[0].isIntersecting) {
        // When the element is in view, start the animation
        setWidths(
          visualParts.map(item => {
            return item.percentage;
          })
        );
      }
    };

    const observer = new IntersectionObserver(handleIntersection, options);

    if (progressLineRef.current) {
      observer.observe(progressLineRef.current);
    }

    return () => {
      if (progressLineRef.current) {
        observer.unobserve(progressLineRef.current);
      }
    };
  }, [visualParts]);

  return (
    <div
      ref={progressLineRef}
      className="progressLine"
      style={{ backgroundColor }}
    >
      {label && <div className="progressLabel">{label}</div>}
      <div className="progressVisualFull">
        {visualParts.map((item, index) => (
          <div
            key={index}
            style={{ width: widths[index] }}
            className={`progressVisualPart ${item.colorClass}`}
          >
            <div className="progressValue">{widths[index]}</div>
          </div>
        ))}
      </div>
    </div>
  );
};

export default ProgressLine;
