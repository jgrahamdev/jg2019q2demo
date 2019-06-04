#!/bin/sh

# Run all the commands to get dev enviroment setup.
npm install 
bower install
gulp copy
gulp concat

# If iTerm is running, open another tab with the current directory and run 'npm start' polling for SASS changes.
if [ "$TERM_PROGRAM" == iTerm.app ]; then
  osascript -e '
    tell app "iTerm"
      activate
      tell the first terminal
        launch session "Default Session"
        tell the last session
          set name to "New Session"
          write text "cd '$TARGET/profiles/demo_framework/themes/$THEMEDEV' && npm start"
        end tell
      end tell
    end tell
  '
fi
