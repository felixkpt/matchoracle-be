function subscribe(eventName: string, listener: EventListener) {
  document.addEventListener(eventName, listener);
}

function unsubscribe(eventName: string, listener: EventListener) {
  document.removeEventListener(eventName, listener);
}

function publish(eventName: string, data: any, moreData: any = null) {

  if (data instanceof Event || (data && data.nativeEvent)) {
    data.preventDefault();
  }

  let detail = data

  if (moreData) {
    detail = { ...detail, moreData }
  }

  const event = new CustomEvent(eventName, { detail });
  document.dispatchEvent(event);
}

export { publish, subscribe, unsubscribe };
