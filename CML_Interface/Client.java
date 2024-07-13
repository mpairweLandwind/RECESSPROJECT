package CML_Interface;

import java.io.*;
import java.net.Socket;
import java.util.*;
import java.util.concurrent.*;

public class Client {
    private static BufferedReader reader;
    private static PrintWriter writer;
    private static BufferedReader userReader = new BufferedReader(new InputStreamReader(System.in));
    private static ExecutorService executorService = Executors.newSingleThreadExecutor();
    private static final Object lock = new Object();

    public static void main(String[] args) {
        try (Socket socket = new Socket("localhost", 8888)) {
            // Initialize reader and writer
            reader = new BufferedReader(new InputStreamReader(socket.getInputStream()));
            writer = new PrintWriter(socket.getOutputStream(), true);

            // Read server welcome message and print it
            System.out.println(readServerResponse());

            // Display available commands to the user
            printCommandsHelp();

            // Client command processing loop
            while (true) {
                String userInput = readUserInput("Enter command: ");
                if (userInput.equalsIgnoreCase("exit")) {
                    System.out.println("Exiting...");
                    break;
                }
                sendCommandToServer(userInput);
                processServerResponses();
            }
        } catch (IOException e) {
            e.printStackTrace();
        } finally {
            executorService.shutdown();
        }
    }

    private static void processServerResponses() throws IOException {
        String response;
        while ((response = readServerResponse()) != null) {
            if (response.startsWith("Challenge")) {
                handleChallenge(response);
            } else if (response.startsWith("availablechallenges ")) {
                handleAvailableChallenges(response.substring("availablechallenges ".length()));
            } else if (response.startsWith("availableapplicants")) {
                handleAvailableApplicants(response.substring("availableapplicants ".length()));
            } else if (response.startsWith("Challenge submitted successfully")) {
                System.out.println(response);
                synchronized (lock) {
                    lock.notify();
                }
                break;
            } else {
                System.out.println(response);
                break; // Ensure complete processing of the server response
            }
        }
    }

    private static void handleChallenge(String response) {
        System.out.println(response); // Display challenge details

        synchronized (lock) {
            executorService.submit(() -> {
                try {
                    List<String> questions = new ArrayList<>();
                    List<String> answers = new ArrayList<>();
                    String username = null;

                    while (true) {
                        String line = readServerResponse();
                        if (line == null) {
                            break;
                        }

                        if (line.startsWith("username: ")) {
                            username = line.substring("username: ".length());
                        } else if (line.startsWith("Question: ")) {
                            System.out.println(line); // Display the question
                        } else if (line.startsWith("Enter your answer:")) {
                            System.out.print(line + " "); // Display the answer prompt
                            String answer = readUserInput("");
                            answers.add(answer);
                            writer.println(answer); // Send the answer to the server
                            writer.flush(); // Ensure the answer is sent to the server
                        } else if (line.equals("Answer received. Press Enter to display the next question.")) {
                            System.out.println(line); // Display the message
                            readUserInput(""); // Wait for the user to press Enter
                            writer.println(""); // Send the Enter key press to the server
                            writer.flush(); // Ensure the Enter key press is sent to the server
                        } else if (line.equals("End of questions")) {
                            break;
                        } else {
                            System.out.println(line); // Display any additional information
                        }
                    }

                    // Submit the collected answers to the server, along with the username
                    submitChallenge(response, questions, answers, username);
                } catch (IOException e) {
                    e.printStackTrace();
                } finally {
                    synchronized (lock) {
                        lock.notify();
                    }
                }
            });

            try {
                lock.wait();
            } catch (InterruptedException e) {
                Thread.currentThread().interrupt();
            }
        }
    }

    private static void submitChallenge(String challengeDetails, List<String> questions, List<String> answers,
            String username) {
        writer.println("SubmitChallenge");
        writer.println(challengeDetails);
        writer.println("username: " + username);
        for (String answer : answers) {
            writer.println(answer);
        }
        writer.flush(); // Ensure all answers are sent to the server
    }

    private static String readServerResponse() throws IOException {
        return reader.readLine();
    }

    private static String readUserInput(String prompt) {
        System.out.print(prompt);
        try {
            return userReader.readLine();
        } catch (IOException e) {
            e.printStackTrace();
            return null;
        }
    }

    private static void sendCommandToServer(String command) {
        writer.println(command);
    }

    private static void handleAvailableChallenges(String statementData) {
        String[] rows = statementData.split("\t");

        // Print table headers
        System.out.println("                           Available Challenges \n");
        System.out.println(
                "Challenge_ID   Title           Description                Status    Number_Of_Questions     start_Date                   End_Date                     Duration  ");

        for (String row : rows) {
            // Split each row into individual columns
            String[] columns = row.split(" ");
            // Print each column separated by spaces
            for (String column : columns) {
                System.out.print(column + "    ");
            }
            System.out.println(); // Move to the next row
        }
        String command = readUserInput("Enter command: ");
        sendCommandToServer(command);
    }

    private static void handleAvailableApplicants(String statementData) {
        String[] rows = statementData.split("\t");

        // Print table headers
        System.out.println("                           Available Applicants \n");
        System.out.println(
                "Username     Firstname      LastName      School Registration Number  ");

        for (String row : rows) {
            // Split each row into individual columns
            String[] columns = row.split(" ");
            // Print each column separated by spaces
            for (String column : columns) {
                System.out.print(column + "    ");
            }
            System.out.println(); // Move to the next row
        }
        String command = readUserInput("Enter command: ");
        sendCommandToServer(command);
    }

    private static void printCommandsHelp() {
        String[] commands = {
                "login <username> <password> - Log in to the system usage e.g login lee lee123.com  ",
                "register <username> <firstname> <lastname> <email> <dob> <school_reg_no> <image_path> - Register a new user",
                "viewChallenges - View all available challenges  e.g  viewchallenges  ",
                "attemptChallenge <challenge_ID> - Attempt a specified challenge e.g attemptchallenge  5 ",
                "viewApplicants - View all applicants pending confirmation",
                "confirm <yes/no> <username> - Confirm or reject an applicant",
                "viewReports - View analytics and reports",
                "exit - Exit the client"
        };

        // Find the maximum width of the command strings
        int maxWidth = 0;
        for (String command : commands) {
            if (command.length() > maxWidth) {
                maxWidth = command.length();
            }
        }
        maxWidth += 4; // Adding padding for aesthetics

        int totalCommands = commands.length;

        // Print the rhombus
        for (int i = 0; i < totalCommands; i++) {
            int paddingFactor = Math.min(i, totalCommands - i - 1);
            printRhombusLine(commands[i], maxWidth, paddingFactor, totalCommands);
        }
    }

    private static void printRhombusLine(String command, int maxWidth, int paddingFactor, int totalCommands) {
        int outerPadding = paddingFactor;
        int commandPadding = (maxWidth - command.length() - 2);

        // Print the leading spaces for the rhombus shape
        for (int j = 0; j < outerPadding; j++) {
            System.out.print(" ");
        }

        // Print the command line with surrounding asterisks
        System.out.print("* ");
        System.out.print(command);
        for (int j = 0; j < commandPadding; j++) {
            System.out.print(" ");
        }
        System.out.println(" *");

        // Print the line of asterisks separating commands, except after the last command
        if (paddingFactor < totalCommands / 2) {
            for (int j = 0; j <= outerPadding; j++) {
                System.out.print(" ");
            }
            for (int j = 0; j < maxWidth - 2 * outerPadding; j++) {
                System.out.print("*");
            }
            System.out.println();
        }
    }
}
